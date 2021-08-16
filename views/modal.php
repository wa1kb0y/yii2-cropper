<?php
use \yii\helpers\ArrayHelper;
/** @var \yii\web\View $this */
$btnCloseShow = ArrayHelper::getValue($widget->modalOptions, 'btnCloseShow', true);
$btnSubmitShow = ArrayHelper::getValue($widget->modalOptions, 'btnSubmitShow', true);
$submitOnClose = ArrayHelper::getValue($widget->modalOptions, 'submitOnClose');
$submitFormId = ArrayHelper::getValue($widget->modalOptions, 'submitFormId');
?>

<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal_<?= $widget->id ?>">
    <?= ArrayHelper::getValue($widget->modalOptions, 'btnLaunchText', Yii::t('cropper', 'MODAL_BTN_LAUNCH')) ?>
</button>

<div class="modal" tabindex="-1" id="modal_<?= $widget->id ?>">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <?= ArrayHelper::getValue($widget->modalOptions, 'header', '') ?>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?= $this->render('widget', [
                    'model' => $model,
                    'widget' => $widget,
                ]) ?>
            </div>
            <?php if ($btnSubmitShow || $btnCloseShow): ?>
            <div class="modal-footer">
                <?php if ($btnCloseShow): ?>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <?= ArrayHelper::getValue($widget->modalOptions, 'btnCloseText', Yii::t('cropper', 'MODAL_BTN_CLOSE')) ?>
                </button>
                <?php endif; ?>
                <?php if ($btnSubmitShow): ?>
                    <button type="button" class="btn btn-primary btn-submit">
                        <?= ArrayHelper::getValue($widget->modalOptions, 'btnSubmitText', Yii::t('cropper', 'MODAL_BTN_SUBMIT')) ?>
                    </button>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
?>

<?php
$js = <<<JS
    $('#modal_$widget->id .btn-submit').on('click', function (event) {
        console.log('test');
        $("#$submitFormId").submit();
    });
JS;
$this->registerJs($js);

if ($submitOnClose) {
    $js = <<<JS
        $('#modal_$widget->id').on('hidden.bs.modal', function (event) {
            $("#$submitFormId").submit();
        });
    JS;
    $this->registerJs($js);
}
?>