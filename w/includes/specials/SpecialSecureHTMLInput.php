<?php
function wfSpecialSecureHTMLInput() {
  global $wgOut;
  global $wgRequest;

  if($wgRequest->GetVal('key') && $wgRequest->GetVal('html')) {
    $html = str_replace("\r\n", "\n", $wgRequest->GetVal('html'));
    $wgOut->addHTML('<form><textarea cols="40" rows="15" readonly>');
    $wgOut->addHTML('&lt;shtml ');
    $wgOut->addHTML(($wgRequest->GetVal('keyname') ? 'keyname="' . $wgRequest->GetVal('keyname') . '" ' : ''));
    $wgOut->addHTML('hash="' . md5($wgRequest->GetVal('key') . $html) . '"&gt;');
    $wgOut->addHTML(htmlspecialchars($html));
    $wgOut->addHTML('&lt;/shtml&gt;');
    $wgOut->addHTML('</textarea></form>' . "\n");
    $wgOut->addHTML('Copy the code above EXACTLY and paste it into the wiki editor.<br>' . "\n");
    $wgOut->addHTML('If the generated code does not work, try removing all linefeeds from the input HTML and re-generate.<br>' . "\n");
    $wgOut->addHTML('<hr>' . "\n");
    $wgOut->addHTML($html);
  } else {
    $wgOut->addHTML('<form method="post">' . "\n");
    $wgOut->addHTML('<b>Key Name</b> (optional): <input name="keyname" size="20"><br>' . "\n");
    $wgOut->addHTML('<b>Key</b>: <input name="key" size="20"><br>' . "\n");
    $wgOut->addHTML('<b>HTML</b>: <textarea name="html" cols="40" rows="15"></textarea><br>' . "\n");
    $wgOut->addHTML('<input type="submit">' . "\n");
    $wgOut->addHTML('</form>' . "\n");
  }
}
?>
