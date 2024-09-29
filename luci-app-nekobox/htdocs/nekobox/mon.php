<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        html, body {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            width: 100%;
            height: 100%;
            font-family: 'Comic Sans MS', cursive, sans-serif;
        }

        body {
            box-sizing: border-box;
            background: #f0f8ff;
        }

        nav {
            background: linear-gradient(145deg, #6a5acd, #87ceeb);
            padding: 5px 20px;
            position: sticky;
            top: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            border-radius: 0;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }

        nav:hover {
            background: linear-gradient(145deg, #87ceeb, #6a5acd);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.3);
        }

        nav ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
            display: flex;
            justify-content: center;
            position: relative;
        }

        nav ul li {
            margin: 0 15px;
            position: relative;
        }

        nav ul li a {
            text-decoration: none;
            color: #ffffff;
            font-size: 16px;
            padding: 8px 15px;
            border-radius: 25px;
            transition: background-color 0.3s, color 0.3s, transform 0.3s, box-shadow 0.3s;
            display: block;
            position: relative;
            overflow: hidden;
        }

        nav ul li a::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 300%;
            height: 300%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.2) 20%, transparent 50%);
            transition: transform 0.3s;
            transform: translate(-50%, -50%) scale(0);
            border-radius: 50%;
            z-index: 0;
        }

        nav ul li a:hover::before {
            transform: translate(-50%, -50%) scale(1);
        }

        nav ul li a:hover {
            background-color: rgba(255, 255, 255, 0.2);
            color: #6a5acd;
            transform: scale(1.1);
        }

        nav ul li a.active {
            background-color: rgba(255, 255, 255, 0.4);
            color: #6a5acd;
            font-weight: bold;
        }

        .submenu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background: linear-gradient(145deg, #6a5acd, #87ceeb);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            border-radius: 0;
            padding: 5px 0;
            width: auto;
            min-width: 160px;
            box-sizing: border-box;
        }

        .submenu li {
            margin: 0;
            padding: 0;
        }

        .submenu li a {
            font-size: 14px;
            padding: 8px 15px;
            width: 100%;
            display: block;
            white-space: nowrap;
            color: #ffffff;
            transition: background-color 0.3s, color 0.3s;
            position: relative;
            overflow: hidden;
            box-sizing: border-box;
        }

        .submenu li a::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.3) 20%, transparent 50%);
            transition: transform 0.3s;
            transform: scaleX(0);
            transform-origin: left center;
            z-index: 0;
        }

        .submenu li a:hover::before {
            transform: scaleX(1);
        }

        .submenu li a:hover {
            background-color: rgba(255, 255, 255, 0.2);
            color: #6a5acd;
        }

        nav ul li:hover .submenu {
            display: block;
        }

        .content {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .cbi-map {
            width: 100vw;
            height: 100vh;
            margin: 0;
            padding: 0;
            overflow: hidden;
            background-color: black;
            position: relative;
        }

        .cbi-map iframe {
            width: 100%;
            height: 100%;
            border: none;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .config-menu-button {
            height: 40px;
            width: 40px;
            border-radius: 50%;
            background-color: transparent;
            border: 5px solid;
            color: #ffcc00;
            position: absolute;
            right: 20px; 
            text-align: center;
            text-decoration: none;
            transition: opacity 0.3s;
            animation: borderAnimation 3s linear infinite;
            display: flex;
            align-items: center;
            justify-content: center;
            top: 20px; 
         }

        .config-menu-button {
            right: 50px; 
        }

        .button-top-60 {
            top: 60px; 

         }

    </style>
</head>
<body>
<nav>
    <a href="/nekobox" class="config-menu-button button-top-60">
        <i> 🏠</i>
    </a>
    <ul>
    <li><a href="?page=upload" class="<?= (!isset($_GET['page']) || $_GET['page'] == 'upload') ? 'active' : '' ?>">Mihomo</a></li>
            <li><a href="?page=upload_sb" class="<?= (isset($_GET['page']) && $_GET['page'] == 'upload_sb') ? 'active' : '' ?>">Sing-box</a></li>
            <li>
                <a href="#" class="<?= (isset($_GET['page']) && in_array($_GET['page'], ['box', 'personal', 'video'])) ? 'active' : '' ?>">Template</a>
                <ul class="submenu">
                    <li><a href="?page=box" class="<?= (isset($_GET['page']) && $_GET['page'] == 'box') ? 'active' : '' ?>">Box</a></li>
                    <li><a href="?page=personal" class="<?= (isset($_GET['page']) && $_GET['page'] == 'personal') ? 'active' : '' ?>">Personal</a></li>
                    <li><a href="?page=video" class="<?= (isset($_GET['page']) && $_GET['page'] == 'video') ? 'active' : '' ?>">Video</a></li>
                </ul>
            </li>
            <li>
                <a href="#" class="<?= (isset($_GET['page']) && in_array($_GET['page'], ['neko_yacd', 'neko_meta'])) ? 'active' : '' ?>">Panel</a>
                <ul class="submenu">
                    <li><a href="?page=neko_yacd" class="<?= (isset($_GET['page']) && $_GET['page'] == 'neko_yacd') ? 'active' : '' ?>">Meta-Yacd</a></li>
                    <li><a href="?page=neko_meta" class="<?= (isset($_GET['page']) && $_GET['page'] == 'neko_meta') ? 'active' : '' ?>">MetaCubeXD</a></li>
                </ul>
            </li>
        </ul>
    </nav>

<div class="content">
    <?php
    if (isset($_GET['page'])) {
        $page = $_GET['page'];
        
        switch ($page) {
            case 'upload':
                include 'upload.php';
                break;

            case 'upload_sb':
                include 'upload_sb.php';
                break;

            case 'box':
                include 'box.php';
                break;

            case 'personal':
                include 'personal.php';
                break;

            case 'video':
                include 'video.php';
                break;

            case 'neko_yacd':
                echo '<div class="cbi-map">
                        <iframe id="neko"></iframe>
                      </div>
                      <script type="text/javascript">
                          fetch("/nekobox/lib/log.php?data=url_dash")
                              .then(response => response.json())
                              .then(data => {
                                  document.getElementById("neko").src = data.yacd;
                              })
                              .catch(error => {
                                  console.error("Error fetching URL data:", error);
                              });
                      </script>';
                break;

            case 'neko_meta':
                echo '<div class="cbi-map">
                        <iframe id="neko"></iframe>
                      </div>
                      <script type="text/javascript">
                          fetch("/nekobox/lib/log.php?data=url_dash")
                              .then(response => response.json())
                              .then(data => {
                                  document.getElementById("neko").src = data.meta;
                              })
                              .catch(error => {
                                  console.error("Error fetching URL data:", error);
                              });
                      </script>';
                break;

            default:
                include 'upload.php';
                break;
        }
    } else {
        include 'upload.php';
    }
    ?>
</div>

</body>
</html>
