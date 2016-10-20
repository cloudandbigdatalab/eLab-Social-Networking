<?php
/**
 * Academia Page Layout
 *
 * Contains CSS for the page shell and page layout
 *
 * Default layout: 990px wide, centered. Used in default page shell
 *
 * @package Elgg.Core
 * @subpackage UI
 */
?>
/* <style> /**/

/* ***************************************
	PAGE LAYOUT
*************************************** */
/***** DEFAULT LAYOUT ******/
<?php // the width is on the page rather than topbar to handle small viewports ?>
.elgg-page-default {
	min-width: 800px;
}
.elgg-page-default .elgg-page-header > .elgg-inner {
	max-width: 990px;
	margin: 0 auto;
	min-height: 12px;
	padding-top: 32px;
}
.elgg-page-default .elgg-page-navbar > .elgg-inner {
	max-width: 990px;
	margin: 0 auto;
	height: auto;
}
.elgg-page-default .elgg-page-body > .elgg-inner {
	max-width: 990px;
	margin: 0 auto;
}
.elgg-page-default .elgg-page-footer > .elgg-inner {
	max-width: 990px;
	margin: 0 auto;
	padding: 0px 0;
	border-top: 1px solid #DEDEDE;
}

/***** TOPBAR ******/
.elgg-page-topbar {
	background: #000;
	border-top: 1px solid #000;
	border-bottom: 1px solid #000;
	padding: 0 20px;
	position: fixed;
	height: 32px;
	z-index: 9000;
	width: 97%;
	box-shadow: 
        inset 0 0 2px 1px rgba(255,255,255,0.08), 
        0 16px 10px -8px rgba(0, 0, 0, 0.6);
}

/***** PAGE MESSAGES ******/
.elgg-system-messages {
	position: fixed;
	top: 32px;
	right: 20px;
	max-width: 500px;
	z-index: 2000;
}
.elgg-system-messages li {
	margin-top: 10px;
}
.elgg-system-messages li p {
	margin: 0;
}

/***** PAGE HEADER ******/
.elgg-page-header {
	padding: 5px 20px 10px;
	position: relative;
	background: #000;
}
.elgg-page-header > .elgg-inner {
	position: relative;
}
/***** PAGE NAVBAR ******/
.elgg-page-navbar {
	padding: 0 1px;
	position: relative;
background:#000;


 box-shadow: 
        inset 0 0 2px 1px rgba(255,255,255,0.08), 
        0 16px 10px -8px rgba(0, 0, 0, 0.6);
}
.elgg-page-navbar > .elgg-inner {
	position: relative;
}

/***** PAGE BODY LAYOUT ******/
.elgg-page-body {
	padding: 0 20px;
}

.elgg-layout {
	min-height: 360px;
}
.elgg-layout-widgets > .elgg-widgets {
	float: right;
}

.elgg-layout-one-sidebar {
/* Size and position */
    width: 980px;
    margin: 60px auto 30px;
    padding: 15px;
    position: relative;

    /* Styles */
    background: #f6f6f6;
    border-radius: 4px;
    color: #7e7975;
    box-shadow:
        0 6px 6px rgba(0,0,0,0.2),        
        0 6px 6px rgba(0,0,0,0.2),        
        0 0 0 12px rgba(255,255,255,0.4); 

}

.elgg-sidebar {
	position: relative;
	padding: 32px 0 20px 30px;
	float: right;
	width: 21.212121%;
	margin: 0;
	border-left: 1px solid #EBEBEB;
}
.elgg-sidebar-alt {
	position: relative;
	padding: 32px 30px 20px 0;
	float: left;
	width: 16.161616%;
	margin: 0 30px 0 0;
	border-right: 1px solid #EBEBEB;
}
.elgg-main {
	position: relative;
	min-height: 360px;
	padding: 12px 0 10px 0;
}
.elgg-main > .elgg-head {
	padding-bottom: 5px;
	border-bottom: 1px solid #EBEBEB;
	margin-bottom: 10px;
}
.elgg-layout-one-sidebar .elgg-main {
	float: left;
	width: 72.525252%;
}
.elgg-layout-two-sidebar .elgg-main {
	float: left;
	width: 50.101010%;
}

/***** PAGE FOOTER ******/
.elgg-page-footer {
	color: #999;
	padding: 0 10px;
	position: relative;
}

.elgg-page-footer a:hover {
	color: #666;
}
