<!DOCTYPE html>
<html lang="es">
<?= $this->element('head') ?>
<body>
    <nav class="top-navbar" style="max-height: 55px; z-index: 1000;">
        <div class="d-flex justify-content-between align-items-center px-3 w-100">
            <div class="d-flex align-items-center gap-2">
                <div class="d-flex justify-content-center align-items-center bg-white rounded-circle shadow-sm" style="width: 42px; height: 42px;">
                    <img class="my-auto" src="<?= $this->Url->build('img/logo.png') ?>" alt="Logo" height="45">
                </div>
                <div class="gap-0 d-flex flex-column">
                    <h2 class="fs-5 m-0">
                        <?= h($systemTitle) ?>
                    </h2>
                    <small class="m-0" style="font-size: 0.75rem; opacity: 0.9;">Soporte interno</small>
                </div>
            </div>
            <?= $this->element('ia') ?>
            <div class="nav-menu d-flex align-items-center gap-3 py-3">
                <?= $this->Html->link('<i class="bi bi-bar-chart"></i> Estadísticas', ['prefix' => false, 'controller' => 'Tickets', 'action' => 'dashboard'], ['escape' => false]) ?>
                <?= $this->Html->link('<i class="bi bi-ticket"></i> Tickets', ['prefix' => false, 'controller' => 'Tickets', 'action' => 'index'], ['escape' => false]) ?>
                <?= $this->Html->link('<i class="bi bi-person"></i> Mi Perfil', ['prefix' => 'Admin', 'controller' => 'Settings', 'action' => 'editUser', $currentUser->id], ['escape' => false]) ?>
            </div>
            <div class="nav-user d-flex align-items-center gap-2">
                <?= $this->Html->link('<i class="bi bi-box-arrow-right"></i> Salir', ['prefix' => false, 'controller' => 'Users', 'action' => 'logout'], ['class' => 'btn-logout', 'escape' => false]) ?>
            </div>
        </div>
    </nav>

    <div class="overflow-auto scroll" style="max-height: calc(100vh - 55px);">
        <?= $this->Flash->render() ?>
        <!-- Loading Spinner -->
        <?= $this->element('loading_spinner') ?>
        <div class="d-flex" style="height: calc(100vh - 55px);">
            <?= $this->fetch('content') ?>
        </div>
    </div>
</body>
</html>
