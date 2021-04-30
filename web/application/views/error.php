<!DOCTYPE html>
<html lang="<?= lang('var.lang') ?>">
<? $this->load->view('base/head'); ?>
<body>
<div class="error404">
    <div class="error"><?= $error_code ?></div>
    <div class="title"><?= $error_heading ?></div>
    <div class="additional"><?= $error_message ?><br><br><?= lang('error.start') ?> <a
            href="<?= site_url() ?>"><?= lang('error.mainpage') ?></a> <?= lang('error.service') ?>.
    </div>
</div>
</body>
</html>