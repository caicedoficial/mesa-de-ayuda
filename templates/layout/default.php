<!DOCTYPE html>
<html lang="es">
<?= $this->element('head') ?>
<body>
    <div class="overflow-auto scroll" style="height: 100dvh;">
        <?= $this->Flash->render() ?>
        <?= $this->fetch('content') ?>
    </div>
</body>
</html>
