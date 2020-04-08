<?php
namespace budyaga\cropper\actions;

use Yii;
use yii\helpers\ArrayHelper;
use Aws\S3\Exception\S3Exception;

class UploadS3Action extends UploadAction
{
    /**
     * S3 storage component
     * @var \bilberrry\spaces\Service
     */
    public $s3;

    /**
     * Path for files on S3
     * @var string
     */
    public $remotePath = '';

    /**
     * Keep a local file of file after uploading to S3
     * @var boolean
     */
    public $keepLocal = false;

    public function run()
    {
        $result = parent::run();

        $localPath = $this->path . $this->model->{$this->uploadParam}->name;
        $remotePath = $this->remotePath . DIRECTORY_SEPARATOR . $this->model->{$this->uploadParam}->name;
        if (file_exists($localPath)) {
            $res = Yii::$app->{$this->s3}->commands()->upload($remotePath, $localPath)->execute();
            $result['filelink'] = $res->get('ObjectURL');
            if (!$this->keepLocal) {
                unlink($localPath);
            }
        }

        return $result;
    }
}
