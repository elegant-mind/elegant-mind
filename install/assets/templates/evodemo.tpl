/**
 * EvoDemo
 *
 * Demo template for Evolution
 *
 * @category	Template
 * @version 	1.0
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@lock_template 0
 * @internal 	@modx_category Templates
 * @internal    @installset sample
 */
<!doctype html>
<html lang="en">
<head>
<meta charset="[(modx_charset)]">
<title>[(site_name)] - [*pagetitle*]</title>
<base href="[(site_url)]">
<!--[if lt IE 9]>
<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<!--[if lt IE 7]>
    <style type="text/css">
        body { behavior: url(assets/js/csshover3.htc) }
    </style>
    <script type="text/javascript" src="assets/js/frankensleight.js"></sript>
<![endif]-->
<link rel="icon" href="favicon.ico" type="image/x-icon">
<link rel="stylesheet" type="text/css" href="assets/templates/evodemo/css/demo2.css">
<script type="text/javascript" src="assets/js/jquery-1.7.2.min.js"></script>
</head>
<body id="page[*id*]">
    <div id="wrapper">
        <header>
            <hgroup>
                <a href="[(site_url)]"><img src="assets/images/logo.png" alt="[(site_name)]" /></a>
                <h3>[*longtitle*]</h3>
            </hgroup>
                {{socialBlock}}
                {{searchBlock}}
        </header>
        <nav>
            [[Wayfinder? &startId=`0` &level=`2` &parentClass=`parent`]]
        </nav>
        <div id="main">
            <section id="mainLeft">
                {{firstBlock}}
                {{secondBlock}}
            </section>
            <section id="mainRight">
                [*content*]
            </section>
        </div> <!-- end main -->
    </div> <!--end wrapper-->
    <footer>
        <section id="footerBar">
            [[Wayfinder? &startId=`0` &level=`1`]]
        </section>
        <section id="copyright">
            <p>&copy; [!CopyYears?startYear=`2011`!] [(site_name)] || Content managed by MODx - MODx&#8482; is licensed under the GPL</p>
        </section>
    </footer>
<script type="text/javascript" src="js/jquery.placeholder.min.js"></script>
<script type="text/javascript" src="js/placeholder.js"></script>	
</body>
</html>