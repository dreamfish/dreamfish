/*
 * ArticleComments.php - A MediaWiki extension for adding comment sections to articles.
 * @author Jim R. Wilson
 * @version 0.4.3
 * @copyright Copyright (C) 2007 Jim R. Wilson
 * @license The MIT License - http://www.opensource.org/licenses/mit-license.php 
 * -----------------------------------------------------------------------
 * Description:
 *     This is a MediaWiki (http://www.mediawiki.org/) extension which adds support
 *     for comment sections within article pages, or directly into all pages.
 * Requirements:
 *     MediaWiki 1.6.x, 1.8.x, 1.9.x or higher
 *     PHP 4.x, 5.x or higher
 * Installation:
 *     1. Drop this script (ArticleComments.php) in $IP/extensions
 *         Note: $IP is your MediaWiki install dir.
 *     2. Enable the extension by adding this line to your LocalSettings.php:
 *            require_once('extensions/ArticleComments.php');
 * Usage:
 *     Once installed, you may utilize ArticleComments by adding the following flag in the article text:
 *         <comments />
 *     Note: Typically this would be placed at the end of the article text.
 * Version Notes:
 *     version 0.4.3:
 *         Added new insertion feature, comments will now be inserted before <!--COMMENTS_ABOVE--> if present
 *         Or, after <!--COMMENTS_BELOW--> if present (the latter causes reverse chronological comment ordering).
 *     version 0.4.2:
 *         Updated default spam filtering code to check all fields against $wgSpamRegex, if specified.
 *     version 0.4.1:
 *         Updated default spam filtering code. (now matches <a> tags in commenterName)
 *     version 0.4:
 *         Updated default spam filtering code.
 *         Abstracted Spam filter via hook (ArticleCommentsSpamCheck) to aid future spam checkers
 *     version 0.3:
 *         Added rudimentary spam filtering based on common abuses.
 *     version 0.2:
 *         Fixed form post method to use localized version of "Special"
 *         Added option for making the form automatically visible (no "Leave a comment..." link)
 *         Added option of diabling the "Website" field
 *         Added system message for prepopulating the comment box.
 *         Added system message for structuring comment submission text.
 *         Added abstracted method for form creation (for insertion into skins)
 *         Added option to "Whitelist" Namespaces for comment submission (as by skin-level form).
 *         Added check for user blocked status prior to comment submission.
 *     version 0.1:
 *         Initial release.
 * -----------------------------------------------------------------------
 * Copyright (c) 2007 Jim R. Wilson
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy 
 * of this software and associated documentation files (the "Software"), to deal 
 * in the Software without restriction, including without limitation the rights to 
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of 
 * the Software, and to permit persons to whom the Software is furnished to do 
 * so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all 
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, 
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES 
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND 
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT 
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, 
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING 
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR 
 * OTHER DEALINGS IN THE SOFTWARE. 
 * -----------------------------------------------------------------------
 */
 
# Confirm MW environment
if (!defined('MEDIAWIKI')) die();
 
# Credits
$wgExtensionCredits['other'][] = array(
    'name'=>'ArticleComments',
    'author'=>'Jim R. Wilson - wilson.jim.r &lt;at&gt; gmail.com',
    'url'=>'http://jimbojw.com/wiki/index.php?title=ArticleComments',
    'description'=>'Enables comment sections on article pages.',
    'version'=>'0.4.1'
);
 
# Add Extension Functions
$wgExtensionFunctions[] = 'wfArticleCommentsParserSetup';
 
# Sets up the ArticleComments Parser hook for <comments />
function wfArticleCommentsParserSetup() {
    global $wgParser;
    $wgParser->setHook( 'comments', 'wfArticleCommentsParserHook' );
}
function wfArticleCommentsParserHook( $text, $params = array(), &$parser ) {
 
    # Generate a comment form for display
    $commentForm = wfArticleCommentForm( $parser->mTitle, $params );
    
    # Hide content from the Parser using base64 to avoid mangling.
    # Note: Content will be decoded after Tidy has finished its processing of the page.
    return '<pre>@ENCODED@'.base64_encode($commentForm).'@ENCODED@</pre>';
}
 
/**
 * Echos out a comment form depending on the page action and namespace.
 * @param Title $title The title of the article on which the form will appear.
 * @param Array $params A hash of parameters containing rendering options.
 */
function displayArticleCommentForm( $title = null, $params = array() ) {
 
    global $wgRequest, $wgArticleCommentsNSDisplayList;
    
    # Short circuit for anything other than action=view or action=purge
    if ($wgRequest->getVal('action') && 
        $wgRequest->getVal('action')!='view' &&
        $wgRequest->getVal('action')!='purge'
    ) return;
    
    # Short-circuit if displayl ist is undefined or null
    if ($wgArticleCommentsNSDisplayList==null) return;
 
    # Use wgTitle if title is not specified
    if ($title==null) {
        global $wgTitle;
        $title = $wgTitle;
    }
 
    # Ensure that the namespace list is an actual list
    $nsList = $wgArticleCommentsNSDisplayList;
    if (!is_array($nsList)) $nsList = array($nsList);
    
    # Display the form
    if (in_array($title->getNamespace(), $nsList)) {
        echo(wfArticleCommentForm($title, $params));
    }
    
}
 
/**
 * Generates and returns an ArticleComment form.
 * @param Title $title The title of the article on which the form will appear.
 * @param Array $params A hash of parameters containing rendering options.
 */
function wfArticleCommentForm( $title = null, $params = array() ) {
 
    global $wgScript, $wgArticleCommentDefaults, $wgContentLang, $wgContLang;
    $wcl = ($wgContentLang ? $wgContentLang : $wgContLang);
 
    # Merge in global defaults if specified    
    if (is_array($wgArticleCommentDefaults) &&
        !empty($wgArticleCommentDefaults)) {
        $tmp = array();
        foreach ($wgArticleCommentDefaults as $k=>$v) {
            $tmp[strtolower($k)] = $v;
        }
        $params = array_merge($tmp, $params);
    }
    
    # Use wgTitle if title is not specified
    if ($title==null) {
        global $wgTitle;
        $title = $wgTitle;
    }
    
    $ac = 'article-comments-';
    $formAction = $wgScript.'?title='.$wcl->getNsText(NS_SPECIAL).':ProcessComment';
 
    # Build out the comment form.
    $content = 
        '<div id="commentForm">'.
        '<form method="post" action="'.$formAction.'">'.
        '<input type="hidden" id="titleKey" name="titleKey" '.
        'value="'.$title->getDBKey().'" />'.
        '<input type="hidden" id="titleNS" name="titleNS" '.
        'value="'.$title->getNamespace().'" />'.
        '<p>'.wfMsgForContent($ac.'name-field').'<br />'.
        '<input type="text" id="commenterName" name="commenterName" /></p>'.
        ($params['showurlfield']=='false' || $params['showurlfield']===false?'':
            '<p>'.wfMsgForContent($ac.'url-field').'<br />'.
            '<input type="text" id="commenterURL" name="commenterURL" value="http://" /></p>'
        ).
        '<p>'.wfMsgForContent($ac.'comment-field').'<br />'.
        '<textarea id="comment" name="comment" style="width:30em" rows="5">'.
        '</textarea></p>'.
        '<p><input id="submit" type="submit" '.
        'value="'.wfMsgForContent($ac.'submit-button').'" /></p>'.
        '</form></div>';
        
    # Short-circuit if noScript has been set to anything other than false
    if (isset($params['noscript']) && 
        $params['noscript']!=='false' &&
        $params['noscript']) {
        return $content;
    }
 
    # Inline JavaScript to make form behavior more rich (must degrade well in JS-disabled browsers)
    $content .= "<script type='text/javascript'>//<![CDATA[\n(function(){\n";
 
    # Prefill the name field if the user is logged in.
    $content .= 
        'var prefillUserName = function(){'."\n".
        'var ptu=document.getElementById("pt-userpage");'."\n".
        'if (ptu) document.getElementById("commenterName").value='.
        'ptu.getElementsByTagName("a")[0].innerHTML;};'."\n".
        'if (window.addEventListener) window.addEventListener'.
        '("load",prefillUserName,false);'."\n".
        'else if (window.attachEvent) window.attachEvent'.
        '("onload",prefillUserName);'."\n";
 
    # Prefill comment text if it has been specified by a system message
    # Note: This is done dynamically with JavaScript since it would be annoying
    # for JS-disabled browsers to have the prefilled text (since they'd have
    # to manually delete it).
    $pretext = wfMsgForContent($ac.'prefilled-comment-text');
    if ($pretext) {
        $content .=
            'var comment = document.getElementById("comment");'."\n".
            'comment._everFocused=false;'."\n".
            'comment.innerHTML="'.htmlspecialchars($pretext).'";'."\n".
            'var clearCommentOnFirstFocus = function() {'."\n".
            'var c=document.getElementById("comment");'."\n".
            'if (!c._everFocused) {'."\n".
            'c._everFocused=true;'."\n".
            'c.value="";}}'."\n".
            'if (comment.addEventListener) comment.addEventListener'.
            '("focus",clearCommentOnFirstFocus,false);'."\n".
            'else if (comment.attachEvent) comment.attachEvent'.
            '("onfocus",clearCommentOnFirstFocus);'."\n";
    }
 
    # Hides the commentForm until the "Make a comment" link is clicked
    # Note: To disable, set $wgArticleCommentDefaults['hideForm']=false in LocalSettings.php
    if (!isset($params['hideform']) || 
        ($params['hideform']!='false' &&
        !$params['hideform']===false)) {
        $content .= 
            'var cf=document.getElementById("commentForm");'."\n".
            'cf.style.display="none";'."\n".
            'var p=document.createElement("p");'."\n".
            'p.innerHTML="<a href=\'javascript:void(0)\' onclick=\''.
            'document.getElementById(\\"commentForm\\").style.display=\\"block\\";'.
            'this.style.display=\\"none\\";false'.
            '\'>'.wfMsgForContent($ac.'leave-comment-link').'</a>";'."\n".
            'cf.parentNode.insertBefore(p,cf);'."\n";
    }
 
    $content .= "})();\n//]]></script>";
    return $content;
}
 
# Attach Hooks
$wgHooks['ParserAfterTidy'][] = 'wfProcessEncodedContent';
$wgHooks['ArticleCommentsSpamCheck'][] = 'defaultArticleCommentSpamCheck';
 
/**
 * Processes HTML comments with encoded content.
 * Usage: $wgHooks['OutputPageBeforeHTML'][] = 'wfProcessEncodedContent';
 * @param OutputPage $out Handle to an OutputPage object presumably $wgOut (passed by reference).
 * @param String $text Article/Output text (passed by reference)
 * @return Boolean Always tru to give other hooking methods a chance to run.
 */
function wfProcessEncodedContent($out, $text) {
    $text = preg_replace(
        '/<pre>@ENCODED@([0-9a-zA-Z\\+\\/]+=*)@ENCODED@<\\/pre>/e',
        'base64_decode("$1")',
        $text
    );
    return true;
}
 
# Sets up special page to handle comment submission
$wgExtensionFunctions[] = 'setupSpecialProcessComment';
function setupSpecialProcessComment() {
    global $IP, $wgMessageCache;
    require_once($IP.'/includes/SpecialPage.php');
    SpecialPage::addPage(new SpecialPage('ProcessComment', '', true, 'specialProcessComment', false));
 
    # Messages used in this extension
    $wgMessageCache->addMessage('article-comments-title-field', 'Title');
    $wgMessageCache->addMessage('article-comments-name-string', 'Name');
    $wgMessageCache->addMessage('article-comments-name-field', 'Name (required): ');
    $wgMessageCache->addMessage('article-comments-url-field', 'Website: ');
    $wgMessageCache->addMessage('article-comments-comment-string', 'Comment');
    $wgMessageCache->addMessage('article-comments-comment-field', 'Comment: ');
    $wgMessageCache->addMessage('article-comments-submit-button', 'Submit');
    $wgMessageCache->addMessage('article-comments-leave-comment-link', 'Leave a comment ...');
    $wgMessageCache->addMessage('article-comments-invalid-field', 'The $1 provided <nowiki>[$2]</nowiki> is invalid.');
    $wgMessageCache->addMessage('article-comments-required-field', '$1 field is required.');
    $wgMessageCache->addMessage('article-comments-submission-failed', 'Comment Submission Failed');
    $wgMessageCache->addMessage('article-comments-failure-reasons', 'Sorry, your comment submission failed for the following reason(s):');
    $wgMessageCache->addMessage('article-comments-no-comments', 'Sorry, the article &quot;[[$1]]&quot; is not accepting comments at this time.');
    $wgMessageCache->addMessage('article-comments-talk-page-starter', "<noinclude>Comments on [[$1]]\n<comments />\n----- __NOEDITSECTION__</noinclude>\n");
    $wgMessageCache->addMessage('article-comments-commenter-said', '$1 said ...');
    $wgMessageCache->addMessage('article-comments-summary', 'Comment provided by $1 - via ArticleComments extension');
    $wgMessageCache->addMessage('article-comments-submission-succeeded', 'Comment submission succeeded');
    $wgMessageCache->addMessage('article-comments-submission-success', 'You have successfully submitted a comment for [[$1]]');
    $wgMessageCache->addMessage('article-comments-submission-view-all', 'You may view all comments on that article [[$1|here]]');
    $wgMessageCache->addMessage('article-comments-prefilled-comment-text', '');
    $wgMessageCache->addMessage('article-comments-user-is-blocked', 'Your user account is currently blocked from editing [[$1]].');
    $wgMessageCache->addMessage('article-comments-new-comment', "\n== \$1 ==\n\n<div class='commentBlock'>\n\$2\n\n--\$3 \$4\n</div>\n");
    $wgMessageCache->addMessage('article-comments-no-spam', "At least one of the submitted fields was flagged as spam.");
    $wgMessageCache->addMessage('processcomment', 'Process Article Comment');
}
 
/**
* Special page for comment processing.
*/
function specialProcessComment() {
 
    global $wgOut, $wgParser, $wgUser, $wgContentLang, $wgContLang;
    $wcl = ($wgContentLang ? $wgContentLang : $wgContLang);
 
    # Retrieve submitted values
    $titleKey = $_POST['titleKey'];
    $titleNS = intval($_POST['titleNS']);
    $commenterName = $_POST['commenterName'];
    $commenterURL = isset($_POST['commenterURL']) ? $_POST['commenterURL'] : '';
    $comment = $_POST['comment'];
 
    # Perform validation checks on supplied fields
    $ac = 'article-comments-';
    $messages = array();
    if (!$titleKey) $messages[] = wfMsgForContent(
        $ac.'invalid-field', wfMsgForContent($ac.'title-field'), $titleKey
    );
    if (!$commenterName) $messages[] = wfMsgForContent(
        $ac.'required-field', wfMsgForContent($ac.'name-string'));
    if (!$comment) $messages[] = wfMsgForContent(
        $ac.'required-field', wfMsgForContent($ac.'comment-string'));
    if (!empty($messages)) {
        $wgOut->setPageTitle(wfMsgForContent($ac.'submission-failed'));
        $wikiText = "<div class='errorbox'>";
        $wikiText .= wfMsgForContent($ac.'failure-reasons')."\n\n";
        foreach ($messages as $message) {
            $wikiText .= "* $message\n";
        }
        $wgOut->addWikiText($wikiText . "</div>");
        return;
    }
 
    # Setup title and talkTitle object
    $title = Title::newFromDBkey($titleKey);
    $title->mNamespace = $titleNS - ($titleNS % 2);
    $article = new Article($title);
 
    $talkTitle = Title::newFromDBkey($titleKey);
    $talkTitle->mNamespace = $titleNS + 1 - ($titleNS % 2);
    $talkArticle = new Article($talkTitle);
 
    # Check whether user is blocked from editing the talk page
    if ($wgUser->isBlockedFrom($talkTitle)) {
        $wgOut->setPageTitle(wfMsgForContent($ac.'submission-failed'));
        $wikiText = "<div class='errorbox'>";
        $wikiText .= wfMsgForContent($ac.'failure-reasons')."\n\n";
        $wikiText .= '* '.wfMsgForContent($ac.'user-is-blocked', $talkTitle->getPrefixedText())."\n";
        $wgOut->addWikiText($wikiText . "</div>");
        return;
    }
 
    # Retrieve article content
    $articleContent = '';
    if ( $article->exists() ) {
        $articleContent = $article->getContent();
    }
 
    # Retrieve existing talk content
    $talkContent = '';
    if ( $talkTitle->exists() ) {
        $talkContent = $talkArticle->getContent();
    }
    
    
    # Check if talk NS is in the Namespace display list
    # Note: if so, then there's no need to confirm that <comments /> appears in the article or talk page.
    global $wgArticleCommentsNSDisplayList;
    $skipCheck = (
        is_array($wgArticleCommentsNSDisplayList) ?
        in_array($talkTitle->getNamespace(),$wgArticleCommentsNSDisplayList):
        false
    );
 
    # Check whether the article or its talk page contains a <comments /> flag
    if (!$skipCheck &&
        preg_match('/<comments( +[^>]*)?\\/>/', $articleContent)===0 &&
        preg_match('/<comments( +[^>]*)?\\/>/', $talkContent)===0
    ) {
        $wgOut->setPageTitle(wfMsgForContent($ac.'submission-failed'));
        $wgOut->addWikiText(
            "<div class='errorbox'>".
            wfMsgForContent($ac.'no-comments', $title->getPrefixedText()).
            "</div>"
        );
        return;
    }
 
    # Run spam checks
    $isspam = false;
	wfRunHooks( 'ArticleCommentsSpamCheck', array( $comment , $commenterName, $commenterURL, &$isspam ) );
 
    # If it's spam - it's gone!
    if ($isspam) {
        $wgOut->setPageTitle(wfMsgForContent($ac.'submission-failed'));
        $wgOut->addWikiText(
            "<div class='errorbox'>".
            wfMsgForContent($ac.'no-spam').
            "</div>"
        );
        return;
    }
    
    # Initialize the talk page's content.
    if ( $talkContent == '' ) {
        $talkContent = wfMsgForContent($ac.'talk-page-starter', $title->getPrefixedText() );
    }
    
    # Determine signature components
    $d = $wcl->timeanddate( date( 'YmdHis' ), false, false) . ' (' . date( 'T' ) . ')';
    if ($commenterURL && $commenterURL!='http://') $sigText = "[$commenterURL $commenterName]";
    else if ($wgUser->isLoggedIn()) $sigText = $wgParser->getUserSig( $wgUser );
    else $sigText = $commenterName;
    
    # Search for insertion point, or append most recent comment.
    $commentText = wfMsgForContent(
        $ac.'new-comment',
        wfMsgForContent($ac.'commenter-said', $commenterName),
        $comment,
        $sigText,
        $d
    );
    $posAbove = stripos( $talkContent, '<!--COMMENTS_ABOVE-->' );
    if ($posAbove===false) $posBelow = stripos( $talkContent, '<!--COMMENTS_BELOW-->' );
    if ($posAbove!==false) {
        # Insert comments above HTML marker
        $talkContent = substr( $talkContent, 0, $posAbove ) . $commentText . substr( $talkContent, $posAbove );
    } else if ($posBelow!==false) {
        # Insert comments below HTML marker
        $talkContent = substr( $talkContent, 0, $posBelow + 21 ) . $commentText . substr( $talkContent, $posBelow + 21 );
    } else {
        # No marker found, append to bottom (default)
        $talkContent .= $commentText;
    }
 
    # Update the talkArticle with the new comment
    $summary = wfMsgForContent($ac.'summary', $commenterName);
    if (method_exists($talkArticle, 'doEdit')) {
        $talkArticle->doEdit($talkContent, $summary);
    } else {
        $method = ($talkArticle->exists() ? 'updateArticle' : 'insertNewArticle' );
        $talkArticle->$method($talkContent, $summary, false, false);
        return;
    }
 
    $wgOut->setPageTitle(wfMsgForContent($ac.'submission-succeeded'));
    $wgOut->addWikiText(wfMsgForContent($ac.'submission-success', $title->getPrefixedText()));
    $wgOut->addWikiText(wfMsgForContent($ac.'submission-view-all', $talkTitle->getPrefixedText()));
}
 
/**
 * Checks ArticleComment fields for SPAM.
 * Usage: $wgHooks['ArticleCommentsSpamCheck'][] = 'defaultArticleCommentSpamCheck';
 * @param String $comment The comment body submitted (passed by value)
 * @param String $commenterName Name of commenter (passed by value)
 * @param String $commenterURL Website URL provided for comment (passed by value)
 * @param Boolean $isspam Whether the comment is spam (passed by reference)
 * @return Boolean Always true to indicate other hooking methods may continue to check for spam.
 */
function defaultArticleCommentSpamCheck($comment, $commenterName, $commenterURL, $isspam) {
 
    # Short-circuit if spam has already been determined
    if ($isspam) return true;
    $fields = array($comment, $commenterName, $commenterURL);
    
    # Run everything through $wgSpamRegex if it has been specified
    global $wgSpamRegex;
    if ($wgSpamRegex) {
        foreach ($fields as $field) {
            if ( preg_match( $wgSpamRegex, $field ) ) return $isspam = true;
        }
    }
 
    # Rudimentary spam protection
    $spampatterns = array(
        '%\\[url=(https?|ftp)://%smi',
        '%<a\\s+[^>]*href\\s*=\\s*[\'"]?\\s*(https?|ftp)://%smi'
    );
    foreach ($spampatterns as $sp) {
        foreach (array($comment, $commenterName, $commenterURL) as $field) {
            if ( preg_match($sp, $field) ) return $isspam = true;
        }
    }
    
    # Check for bad input for commenterName (seems to be a popular spam location)
    $spampatterns = array(
        '%<a\\s+%smi',
        '%(https?|ftp)://%smi',
        '%(\\n|\\r)%smi'
    );
    foreach ($spampatterns as $sp) if ( preg_match($sp, $commenterName) ) return $isspam = true;
    
    # Fail for length violations
    if ( strlen($commenterName)>255 || strlen($commenterURL)>300 ) return $isspam = true;
    
    # We made it this far, leave $isspam alone and give other implementors a chance.
    return true;
}
 
//
