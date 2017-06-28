<?php $OUTPUT->doctype(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <title><?php get_string('configtitle', 'theme_resilience') ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->pix_url('favicon', 'theme')?>" />
    <?php echo $OUTPUT->standard_head_html() ?>
</head>
<body id="<?php p($PAGE->bodyid) ?>" >

  <div class="image-container">
   	<div id="page" class="container-fluid">
        <!-- Start Main Regions -->
    	<div id="page-content" class="row-fluid">
    	    <section id="region-main" class="span12">
                <?php
                echo $OUTPUT->course_content_header();
                echo $OUTPUT->main_content();
                echo $OUTPUT->course_content_footer();
                ?>
            </section>
        </div>
        <!-- End Main Regions -->
	</div>
  </div>
</div>

<div id="page-footer" class="clearfix">

    <?php
    $description = get_string('backgrounddesc', 'theme_resilience');
    $copyright = get_string('ogl1', 'theme_resilience');
    $url = new moodle_url('https://www.nationalarchives.gov.uk/doc/open-government-licence/version/1/');
    $html = html_writer::start_div('image_copyright');
    $html .= $description . ' - ';
    $html .= html_writer::link($url, $copyright, array('class'=>'background_copyright', 'target'=>'_blank'));
    $html .= html_writer::end_div();
    echo $html;
    echo $OUTPUT->standard_footer_html();
    ?>
</div>
<?php echo $OUTPUT->standard_end_of_body_html() ?>
</body>
</html>