<div class="wrap">
    <h1><?= $heading ?></h1>
    <div id="poststuff">
        <!--<div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
                <form action="options.php" method="post">
                    <?php settings_fields($settings_group); ?>
                    <?= $fields ?>
                    <div class="submit-wrap">
                        <?php submit_button($submit_text); ?>
                        <div class="spinner"></div>
                    </div>
                </form>
            </div>
        </div>-->
        <br class="clear">
        <h1>References:</h1>
        <ul class="references__list">
            <li><strong>FILENAME:</strong></li>
		    <?php
            $upload_dir = wp_upload_dir();
		    $path_dir   = $upload_dir['basedir'] . '/pdfs';
		    $path_url   = $upload_dir['baseurl'] . '/pdfs';
		    foreach($list as $p)
		    {
                echo '<li><a href="' . $path_url . '/' . $p . '" target="_blank">' . $p . '</a><button class="remove-pdf" data-name="' . $p . '">Delete</button></li>';
            }
		    ?>
        </ul>
        <div class="pagination">

        </div>
        <div class="info"></div>
    </div>
</div>
