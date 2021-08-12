<?php
/**
 * All Pages
 *
 * Displays all pages
 *
 * @package GetSimple Extended
 * @subpackage Page-Edit
 */

// Setup inclusions
$load['plugin'] = true;

// Include common.php
include('inc/common.php');

// Variable settings
login_cookie_check();
$id = filter_input(INPUT_GET, 'id');
$ptype = filter_input(INPUT_GET, 'type');
$path = GSDATAPAGESPATH;
$counter = '0';
$table = '';

# clone attempt happening
if (isset($_GET['action']) && isset($_GET['id']) && $_GET['action'] == 'clone') {
	// check for csrf
	if (!defined('GSNOCSRF') || (GSNOCSRF == false)) {
		if (!check_nonce($_GET['nonce'], 'clone', 'pages.php')) die('CSRF detected!');
	}
	# check to not overwrite
	$count = 1;
	$newfile = GSDATAPAGESPATH . $_GET['id'] . '-' . $count . '.xml';
	if (file_exists($newfile)) {
		while (file_exists($newfile)) {
			$count++;
			$newfile = GSDATAPAGESPATH . $_GET['id'] . '-' . $count . '.xml';
		}
	}
	$newurl = $_GET['id'] . '-' . $count;
	# do the copy
	$status = copy($path . $_GET['id'] . '.xml', $path . $newurl . '.xml');
	if ($status) {
		$newxml = getXML($path . $newurl . '.xml');
		$newxml->url = $newurl;
		$newxml->title = $newxml->title . ' [' . i18n_r('COPY') . ']';
		$newxml->pubDate = date('r');
		$newxml->creDate = date('r');
		$newxml->author = $USR;
		$newxml->publisher = $USR;
		$newxml->attributes()->revisionNumber = 1;
		$newxml->attributes()->appName = $site_full_name;
		$newxml->attributes()->appVersion = $site_version_no;
		exec_action('page-clone');
		$status = XMLsave($newxml, $path . $newurl . '.xml');
		if ($status) {
			create_pagesxml('true');
			generate_sitemap();
			exec_action('page-clone-success');
			header('Location: pages.php?upd=clone-success&id=' . $newurl);
		}
	}
	if (!$status) {
		exec_action('page-clone-error');
		header('Location: pages.php?error=' . sprintf(i18n_r('CLONE_ERROR'), $_GET['id']));
	}
}

getPagesXmlValues(true);

$count = 0;
foreach ($pagesArray as $page) {
	if ($page['parent'] != '') {
		$parentTitle = returnPageField($page['parent'], 'title');
		$sort = $parentTitle . ' ' . $page['title'];
		$sort = $parentTitle . ' ' . $page['title'];
	} else {
		$sort = $page['title'];
	}
	$page = array_merge($page, array('sort' => $sort));
	$pagesArray_tmp[$count] = $page;
	$count++;
}
// $pagesArray = $pagesArray_tmp;
$pagesSorted = subval_sort($pagesArray_tmp, 'sort');
$table = get_pages_menu('', '', 0);

get_template('header', cl($SITENAME) . ' &raquo; ' . i18n_r('PAGE_MANAGEMENT'));

?>
<?php include('template/include-nav.php'); ?>
<div class="bodycontent">
	<div id="maincontent">
	<?php exec_action('pages-main'); ?>
		<div class="main">
			<h3 class="floated"><?php i18n('PAGE_MANAGEMENT'); ?></h3>
			<div class="edit-nav clearfix">
				<a href="#" id="filtertable" accesskey="<?php echo find_accesskey(i18n_r('FILTER'));?>"><?php i18n('FILTER'); ?></a>
				<a href="#" id="toggle-status" data-role="toggle" data-target="page-status" accesskey="<?php echo find_accesskey(i18n_r('TOGGLE_STATUS'));?>"><?php i18n('TOGGLE_STATUS'); ?></a>
				<a href="#" id="toggle-url" data-role="toggle" data-target="page-url" accesskey="<?php echo find_accesskey(i18n_r('TOGGLE_URL'));?>"><?php i18n('TOGGLE_URL'); ?></a>
			</div>
			<div id="filter-search">
				<form><input type="text" autocomplete="off" class="text" id="q" placeholder="<?php echo strip_tags(lowercase(i18n_r('FILTER'))); ?>..." /> &nbsp; <a href="pages.php" class="cancel"><?php i18n('CANCEL'); ?></a></form>
			</div>
			
			<table id="editpages" class="edittable highlight paginate">
				<tr><th><?php i18n('PAGE_TITLE'); ?></th><th style="text-align:right;" ><?php i18n('DATE'); ?></th><th></th><th></th></tr>
				<?php echo $table; ?>
			</table>
			<p><em><b><span id="pg_counter"><?php echo $count; ?></span></b> <?php i18n('TOTAL_PAGES'); ?></em></p>
			
		</div>
	</div><!-- end maincontent -->
	<div id="sidebar">
		<?php include('template/sidebar-pages.php'); ?>
	</div>
</div>
<?php get_template('footer'); ?>