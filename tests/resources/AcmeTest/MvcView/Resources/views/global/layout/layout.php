<html>
<head>
<?php
if($this->headers)
    foreach($this->headers as $header)
        echo $header."\n";
?>
</head>
<body>
<?= $this->content ?>
</body>
</html>
