<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-quiz-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>

<div id="tge-control-panel">
    <div id="tge-cp-title" class="tge-purple">
        <div class="tge-title-wrapper">
            <p><strong>Thrive</strong> Quiz Builder</p>
        </div>
    </div>
    <div id="tge-graph-items">
        <h4 class="tge-cp-subtitle"><?php echo __( 'Your Questions', Thrive_Graph_Editor::T ) ?></h4>
        <div class="tvd-row">
            <ul id="tge-items-list" class="tvd-collection"></ul>
        </div>
    </div>
    <div id="tge-cp-footer" class="tge-dark-gray">
        <div class="tvd-v-spacer"></div>
        <div class="tvd-row">
            <div class="tvd-col tvd-s12">
                <a id="tge-save-exit" class="tvd-btn tvd-full-btn tge-purple" href="<?php echo tge()->get_editor()->get_quiz_dash_url(); ?>">
					<?php echo __( 'Save & Exit', Thrive_Graph_Editor::T ) ?>
                </a>
            </div>
        </div>
        <div class="tvd-row">
            <div class="tvd-col tvd-s12">
                <p id="tge-saving-status" class="tvd-tooltipped tvd-center-align" data-position="top"
                   data-tooltip="<?php echo __( 'Every change you make is automatically saved.', Thrive_Graph_Editor::T ) ?>">
                    <span class="tvd-icon-question-circle"></span>
                    <span><?php echo __( 'All your changes are auto saved', Thrive_Graph_Editor::T ) ?></span>
                </p>
            </div>
        </div>
    </div>
    <a id="tge-slide-cp" href="javascript:void(0)"></a>
    <div class="tge-scroll tge-scroll-right" data-dir="right"></div>
</div>
