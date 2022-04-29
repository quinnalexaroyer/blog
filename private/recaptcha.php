<?php
function verifyRecaptcha() {
  global $SecretKey;
  $query = array('secret'=>$SecretKey, 'response'=>$_POST['g-recaptcha-response'], 'remoteip'=>$_SERVER['REMOTE_ADDR']);
  $options = array(
    'http'=>array(
      'method'=>"POST",
      'header'=>"Accept-language: en\r\n" .
                "Cookie: foo=bar\r\n",
      'content'=>http_build_query($query)
    )
  );
  $context = stream_context_create($options);
  $fp = fopen('https://www.google.com/recaptcha/api/siteverify', 'r', FALSE, $context);
  $result = json_decode(fpassthru($fp));
  fclose($fp);
  return $result["success"];
}

function loadJavascriptRecaptcha() {
?>
<script src="https://www.google.com/recaptcha/api.js"></script>
<script>
  function onSubmit(token) {
    $('.g-recaptcha').submit();
  }
</script>
<?php
}

function recaptchaButtonData() {
  return ' data-sitekey="6Ld_aqIeAAAAAOCPvjk5Ahj0AlOH-Q4ZhzdeT5zW" data-callback="onSubmit" data-action="submit"' . "\n";
}

function headerRecaptcha() {
  if(shouldApplyRecaptcha()) loadJavascriptRecaptcha();
}

function buttonRecaptcha() {
  if(shouldApplyRecaptcha()) return recaptchaButtonData();
  else return "";
}
?>

