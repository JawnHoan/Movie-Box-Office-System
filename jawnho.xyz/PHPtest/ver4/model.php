<?php
    require "./service/sqlHandler.php";

    # 构造全局SQLKeys实例
    # 可在各业务模型中修改该实例中的属性值
    $keyParams = new SQLKeys();

    # SQL的模糊查询关键字
    $sql_wildcard = "%";

    # ==========================================
    # |                日期生成器                |
    # ==========================================
    function dateGen($wildcard="", ...$times) {
        $date = "'";
        $connector = "-";
        $count = 0;
        foreach ($times as $t) {
            if ($t == end($times))
                $date .= strval($t);
            else
                $date .= strval($t).$connector;
        }
        $date .= $wildcard;
        $date .= "'";
        return $date;
    }

    # ==========================================
    # |               图片存取业务               |
    # ==========================================
    function imageStore($con, $username, $base64_content) {
        /* 
         * $con: 数据库连接实例
         * $base64_content: 图片的base64编码字符串
         * 返回: 存储成功（若中间出现问题则返回失败）
         */
        $md5_val = md5($base64_content);    # 生成作为主键的MD5码
        $sql = "INSERT IGNORE INTO ".
            image_table."(username,img_values,md5) ".
            "VALUES('$username', '$base64_content', '$md5_val');";

        # 操作写入log文件中
        $f = file_put_contents("./log/postTest.log", $sql);
        if (!$f)
            # LOG写入失败，返回失败信息
            return false;

        # 检查SQL语句的合法性
        try {
            $stmt = $con->prepare($sql);
        }
        catch (Exception $e) {
            echo $sql;
            die("sql语句有问题，请重新检查!");  
        }
        $stmt->execute();

        return true;    # 返回成功信息
    }

    function imageLoad($con, $username, $sqlGen) {
        /* 
         * $con: 数据库连接实例
         * $username: 用户名
         * $sqlGen: SQLGenerator实例
         * 返回: 存储的所有图片内容
         */
        global $keyParams;
        
        # 设置生成SQL语句的参数
        $keyParams->setKeys(
            $Main = None, 
            $Where = array([["username", "'$username'"]])
        );
        $sqlGen->setParams(image_table, $keyParams);
        $sql = $sqlGen->getSQL($con);
        # echo $sql;

        # 收集返回的图片数据
        $img_list = array();
        foreach ($con->query($sql) as $row) {
            array_push($img_list, $row["img_values"]);
        }
        return $img_list;
    }
    
    # ==========================================
    # |                可视化业务                |
    # ==========================================

    # ----- 业务1：显示季度/月度题材电影票房占比 -----
    function TypeMovie($con, $Params, $sqlGen) {
        /* 
         * $con: 数据库连接实例
         * $Params: SQL构造参数表
         * $sqlGen: SQLGenerator实例
         */
        global $keyParams;
        global $sql_wildcard;

        # ============ 有效时间字段的选择 ============
        if ($Params["valid"] == 1)
            # 只有年份为有效字段
            $time = [$Params["year"], []];
        elseif ($Params["valid"] == 2) {
            # 年份和季度为有效字段
            $time = [$Params["year"]];
            $s = $Params["season"];
            if ($s == "第一季")
                array_push($time, ["01", "02", "03"]);
            elseif ($s == "第二季")
                array_push($time, ["04", "05", "06"]);
            elseif ($s == "第三季")
                array_push($time, ["07", "08", "09"]);
            elseif ($s == "第四季")
                array_push($time, ["10", "11", "12"]);
            else
                return array("ERROR: Incorrect season input!");
        }
        elseif ($Params["valid"] == 3)
            # 年份和月份为有效字段
            $time = [$Params["year"], [$Params["month"]]];
        else
            # 不合格字段
            return array("ERROR: Invalid key \"valid\"!".
                            "Please check the key value...");

        # ============= 生成参数表 =============
        $whr = array();
        if (empty($time[1])) {
            # 只有年份数据
            array_push($whr, ["release_date", 
                dateGen($sql_wildcard, $time[0]), "LIKE"]);
        } else {
            # 除了年份数据，还有若干个月份数据
            foreach ($time[1] as $m) {
                array_push($whr, ["release_date", 
                    dateGen($sql_wildcard, $time[0], $m), "LIKE"]);
            }
        }
        $keyParams->setKeys(
            $Main = "movie_type, total_box", 
            $Where = array($whr)
        );
        
        # ============= 生成SQL语句 =============
        $sqlGen->setParams(collective_table, $keyParams);
        $sql = $sqlGen->getSQL($con);
        # echo $sql;
        
        # ========== 统计不同题材的电影数据 ==========
        $sum_box = 0;
        $dict = array();
        foreach ($con->query($sql) as $row) {
            $sum_box += $row["total_box"];
            $types = explode(",", $row["movie_type"]);
            foreach ($types as $t) {
                if ($dict[$t])
                    $dict[$t] += $row["total_box"];
                else
                    $dict[$t] = $row["total_box"];
            }
        }
        arsort($dict);  # 对数组按照键值排序
        # print_r($dict);

        # ============= 封装传递的数据 =============
        $threshold = 5; # 百分数
        $other_box = 0;
        $other_ratio = 0;
        $sum_box = array_sum($dict);
        $cat = array();
        $box = array();
        $por = array();
        foreach (array_keys($dict) as $key) {
            $this_box = $dict[$key];
            $ratio = $this_box / $sum_box;

            if (($percent = $ratio*100) > $threshold) {
                # 统计票房占比 > 5%的题材
                array_push($cat, $key);
                array_push($box, $this_box);
                array_push($por, strval($percent)."%");
            }
            else
                # 统计其他题材
                $other_box += $dict[$key];
        }

        # =========== 对其他部分进行统计和封装 ===========
        $other_ratio = $other_box / $sum_box;
        array_push($cat, "其他");
        array_push($box, $other_box);
        array_push($por, strval($other_ratio*100)."%");

        return array("category"=>$cat, 
                    "boxOffice"=>$box, 
                    "portion"=>$por);
    }

    # -------- 业务2：按年份显示月票房折线图 --------
    function lineGraph($con, $year, $sqlGen) {
        /* 
         * $con: 数据库连接实例
         * $Params: SQL构造参数表
         * $sqlGen: SQLGenerator实例
         */
        global $keyParams;
        global $sql_wildcard;

        # 第一步：找到对应年份的电影名 
        $keyParams->setKeys(
            $Main = "movie_name", 
            $Where = array([
                        ["release_date", dateGen($sql_wildcard, $year), "LIKE"],
                        ["release_date", dateGen($sql_wildcard, $year, 12), "LIKE"],
                        ["release_date", dateGen($sql_wildcard, $year, 11), "LIKE"]
                    ])
        );
        $sql_1 = $sqlGen->setParams(collective_table, $keyParams);
        $sql_1 = $sqlGen->getSQL($con);
        # echo $sql_1;
        $names = array();
        foreach ($con->query($sql_1) as $row)
            array_push($names, $row[$keyParams->main]);
        
        # 第二步：针对每一条电影名
        # 去对应的电影表下找票房数据
        # 求和并加入到结果中
        $month_box = array("01"=>0.0, "02"=>0.0, "03"=>0.0, 
                        "04"=>0.0, "05"=>0.0, "06"=>0.0, 
                        "07"=>0.0, "08"=>0.0, "09"=>0.0, 
                        "10"=>0.0, "11"=>0.0, "12"=>0.0
                        );
        foreach ($names as $name) {
            for ($month = 1; $month <= 12; ++$month) {
                if ($month < 10)
                    $month = "0".$month;
                $keyParams->setKeys(
                    $Main = "box_office",
                    $Where = array([["box_date", 
                            dateGen($sql_wildcard, $year, $month), "LIKE"]]),
                    $Dist = true,
                    $func = "SUM"
                );
                $sql_n = $sqlGen->setParams($name, $keyParams);
                $sql_n = $sqlGen->getSQL($con);
                # echo $sql_n, "<br>";
                if ($sum = $con->query($sql_n)) {
                    $month_box[$month] += $sum->fetch()[0];
                }
            }
        }
        
        # 最后，返回月度票房数据列表
        return $month_box;
    }

    # ------- 业务3: 显示指定年份票房Top电影 -------
    function topMovie($con, $Params, $sqlGen) {
        /* 
         * $con: 数据库连接实例
         * $Params: SQL构造参数表
         * $sqlGen: SQLGenerator实例
         */
        global $keyParams;
        global $sql_wildcard;

        # ============ 生成参数表 ============
        $keyParams->setKeys(
            $Main = "movie_name, total_box",
            $Where = array([["release_date", 
                    dateGen($sql_wildcard, $Params["year"]), 
                    "LIKE"]]
            ),
            $Dist = true,
            $func = "", 
            $Odr = ["total_box", "desc"],
            $Lim = $Params["top"]
        );
        # ============ 构建SQL语句 ============
        $sqlGen->setParams(collective_table, $keyParams);
        $sql = $sqlGen->getSQL($con);
        # echo $sql;

        # ============ 封装返回数据 ============
        $ret = array();
        foreach ($con->query($sql) as $row) {
            $tuple = array("name"=>$row["movie_name"], 
                "total_box"=>$row["total_box"]
            );
            array_push($ret, $tuple);
        }
        return $ret;
    }

    # -------- 业务4：显示指定年份的劳模演员 --------
    function topPerformers($con, $Params, $sqlGen) {
        /* 
         * $con: 数据库连接实例
         * $Params: SQL构造参数表
         * $sqlGen: SQLGenerator实例
         */
        global $keyParams;
        global $sql_wildcard;
        
        # ============ 生成参数表 ============
        $keyParams ->setKeys(
            $Main = "performer, count(*)",
            $Where = array([["release_date", 
                dateGen($sql_wildcard, $Params["year"]), "LIKE"]]
            ),
            $Dist = true,
            $func = "", 
            $Odr = ["count(*)", "desc"],
            $Lim = $Params["top"],
            $GB = "performer"
        );

        # ============ 构建SQL语句 ============
        $sqlGen->setParams(performer_table, $keyParams);
        $sql = $sqlGen->getSQL($con);
        # echo $sql;

        # ============ 封装返回数据 ============
        $ret = array();
        foreach ($con->query($sql) as $row) {
            $tuple = array("name"=>$row["performer"], 
                "times"=>$row["count(*)"]
            );
            array_push($ret, $tuple);
        }
        
        return $ret;
    }

?>