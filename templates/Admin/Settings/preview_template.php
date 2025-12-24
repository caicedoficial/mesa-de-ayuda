<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Vista Previa - <?= h($template->template_key) ?></title>
</head>
<body class="overflow-auto scroll">
    <div class="m-4 p-3 rounded shadow">
        <h4>Vista Previa: <?= h($template->template_key) ?></h4>
        <p class="fw-light m-0 small">Esta es una vista previa con datos de ejemplo</p>
    </div>

    <div class="m-4 p-4 border rounded shadow-sm" style="background: #fff;">
        <?= $previewBody ?>
    </div>
</body>
</html>
