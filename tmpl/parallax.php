<?php
/*------------------------------------------------------------------------

# TZ Portfolio Extension

# ------------------------------------------------------------------------

# author    DuongTVTemPlaza

# copyright Copyright (C) 2012 templaza.com. All Rights Reserved.

# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL

# Websites: http://www.templaza.com

# Technical Support:  Forum - http://templaza.com/Forum

-------------------------------------------------------------------------*/
// no direct access
defined('_JEXEC') or die;
$doc = JFactory::getDocument();

$modID = $module->id;
$follow = $params->get('follow_us', 1);
$parallax_bg = $params->get('parallax_background');
$parallax_padding = $params->get('parallax_padding');
$parallax_overlay = $params->get('parallax_overlay','#000000');
$parallax_opacity = $params->get('parallax_opacity',0.5);
$global_color_overlay = modTzTwitterWidgetHelper::hex2rgba($parallax_overlay,$parallax_opacity);
$parallax_style = '
    #TzMod'.$modID.' .tztwd_overlay{
    padding:'.$parallax_padding.';
    background:'.$global_color_overlay.';
    }
';
if (trim($parallax_style)) {
    modTzTwitterWidgetHelper::addExtraCSS($parallax_style,'custom');
}

$doc->addScript('modules/mod_tz_twitterwidget/js/jquery.parallax-1.1.3.js');
?>
<div id="TzMod<?php echo $modID; ?>">
    <div class="tztwd-container tztwd_parallax" >
        <div class="tztwd" style="background-image: url('<?php echo $parallax_bg; ?>')">
            <div class="tztwd_overlay">
            <?php if($params->get('header', 1)) : ?>
                <div class="tztwd-header">
                    <?php if($params->get('twitter_icon', 1)) : ?>
                        <div class="tztwd-twitter-icon"><a href="http://twitter.com" target="_blank">twitter</a></div>
                    <?php endif; ?>
                    <?php if($params->get('type', 1)) : ?>
                        <a href="https://twitter.com/<?php echo $data->tweets[0]->screenName; ?>" target="_blank">
                            <img src="<?php echo $data->tweets[0]->profileImage; ?>" class="tztwd-avatar" />
                            <span class="tztwd-display-name"><?php echo $data->tweets[0]->displayName; ?></span>
                            <span class='tztwd-screen-name'> @<?php echo $data->tweets[0]->screenName; ?></span>
                        </a>
                        <div style="clear: both;"></div>
                    <?php else: ?>
                        <?php if($params->get('link_title', 1)) : ?>
                            <a href="https://twitter.com/search/<?php echo $params->get('query', ''); ?>" target="_blank"><?php echo $params->get('title', '') ?></a>
                        <?php else: ?>
                            <?php echo $params->get('title', ''); ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <div class="tztwd-tweets">
                <?php foreach($data->tweets as $key => $tweet): ?>
                <div class="tztwd-tweet-container <?php echo end(array_keys($data->tweets)) == $key?' tztwd-last':'';?>">
                    <?php if($params->get('avatars', 1)): ?>
                    <div class="avata-info">
                        <a href="https://twitter.com/intent/user?screen_name=<?php echo $tweet->screenName; ?>" target="_blank">
                            <img src="<?php echo $tweet->profileImage; ?>" class="tztwd-avatar" style="width: 35px;" />
                        </a>
                    </div>
                    <div class="tztwd-tweet">
                        <?php else: ?>
                        <div class="tztwd-tweet">
                            <?php endif; ?>
                            <?php if($params->get('display_name', 1)): ?>
                                <a href="https://twitter.com/intent/user?screen_name=<?php echo $tweet->screenName; ?>" target="_blank"><?php echo $tweet->screenName; ?></a>

                            <?php endif; ?>
                            <?php echo $tweet->text; ?>
                        </div>
                        <div class="tztwd-tweet-data">
                            <?php if($params->get('timestamps', 1)): ?>
                                <a href="https://twitter.com/<?php echo $tweet->screenName; ?>/statuses/<?php echo $tweet->id; ?>" target="_blank"><?php echo $tweet->time; ?></a>
                                <?php if($params->get('reply', 1) || $params->get('retweet', 1) || $params->get('favorite', 1)): ?>
                                    &bull;
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if($params->get('reply', 1)): ?>
                                <a href="https://twitter.com/intent/tweet?in_reply_to=<?php echo $tweet->id; ?>" target="_blank"><?php echo JText::_('TZ_TWEET_REPLY');?></a>
                                <?php if($params->get('retweet', 1) || $params->get('favorite', 1)): ?>
                                    &bull;
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if($params->get('retweet', 1)): ?>
                                <a href="https://twitter.com/intent/retweet?tweet_id=<?php echo $tweet->id; ?>" target="_blank"><?php echo JText::_('TZ_RETWEET');?></a>
                                <?php if($params->get('favorite', 1)): ?>
                                    &bull;
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if($params->get('favorite', 1)): ?>
                                <a href="https://twitter.com/intent/favorite?tweet_id=<?php echo $tweet->id; ?>" target="_blank"><?php echo JText::_('TZ_TWEET_FAVORITE');?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if($follow==1){ ?>
                    <div class="twitter-flow">
                        <div class="flow-content">
                            <a href="https://twitter.com/intent/user?screen_name=<?php echo $tweet->screenName; ?>" class="twitter-follow" data-show-count="false" data-lang="en"><?php echo JText::_('TZ_TWEET_FOLLOW');?></a>

                            <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
                        </div>
                    </div>
                <?php } ?>
            </div>

            </div>
        </div>
    </div>

    <?php

$doc->addScriptDeclaration("
jQuery(window).load(function(){
    jQuery('#TzMod$modID .tztwd').each(function(){
    jQuery(this).parallax('30%', 0.4);
    })
})
");