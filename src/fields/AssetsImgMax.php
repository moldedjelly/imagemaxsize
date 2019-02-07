<?php
/**
 * Image Max Size plugin for Craft CMS 3.x
 *
 * Prevent images being uploaded that are bigger than a certain size
 *
 * @link      http://moldedjelly.com
 * @copyright Copyright (c) 2019 MoldedJelly
 */

namespace moldedjelly\imagemaxsize\fields;

use moldedjelly\imagemaxsize\ImageMaxSize;

use Craft;
use craft\fields\Assets;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\base\Volume;
use craft\elements\Asset;
use craft\elements\db\AssetQuery;
use craft\elements\db\ElementQuery;
use craft\errors\InvalidSubpathException;
use craft\errors\InvalidVolumeException;
use craft\helpers\Assets as AssetsHelper;
use craft\helpers\FileHelper;
use craft\helpers\Html;
use craft\web\UploadedFile;





/**
 * @author    MoldedJelly
 * @package   ImageMaxSize
 * @since     1.0.0
 */
 class AssetsImgMax extends Assets
 {

   /**
    * @var array|null References for files uploaded as data strings for this field.
    */
   private $_uploadedDataFiles;


   /**
    * @inheritdoc
    */
   public static function displayName(): string
   {
       return Craft::t('app', 'AssetsImgMax');
   }


   /**
    * @inheritdoc
    */
   public function getElementValidationRules(): array
   {
       $rules = parent::getElementValidationRules();
       $rules[] = 'validateImageDimensions';

       return $rules;
   }


   /**
    * Validates the files to make sure they are one of the allowed file kinds.
    *
    * @param ElementInterface $element
    */
   public function validateImageDimensions(ElementInterface $element)
   {
       $filenames = [];
       $widths = [];
       $heights = [];

       // Get all the value's assets' filenames
       /** @var Element $element */
       /** @var AssetQuery $value */
       $value = $element->getFieldValue($this->handle);
       foreach ($value->all() as $asset) {
           /** @var Asset $asset */
           $filenames[] = $asset->filename;
           $widths[] = $asset->width;
           $heights[] = $asset->height;
       }

       // Get any uploaded filenames
       $uploadedFiles = $this->_getUploadedFiles($element);
       foreach ($uploadedFiles as $file) {
           $filenames[] = $file['filename'];
           if (isset($file['location'])) {
             list($width, $height, $type, $attr) = getimagesize($file['location']);
             $widths[] = $width;
             $heights[] = $height;
           } else {
             $widths[] = 0;
             $heights[] = 0;
           }
       }

       for($i=0; $i<count($filenames); $i++) {
         $filename = $filenames[$i];

         if ($widths[$i] > 6000 || $heights[$i] > 6000) {
           $element->addError($this->handle, Craft::t('app', '"{filename}" image is too big - must fit within 6000x6000px.', [
               'filename' => $filename
           ]));
         }

       }
   }





   // Private Methods
   // =========================================================================

   /**
    * Returns any files that were uploaded to the field.
    *
    * @param ElementInterface $element
    * @return array
    */
   private function _getUploadedFiles(ElementInterface $element): array
   {
       /** @var Element $element */
       $uploadedFiles = [];

       // Grab data strings
       if (isset($this->_uploadedDataFiles['data']) && is_array($this->_uploadedDataFiles['data'])) {
           foreach ($this->_uploadedDataFiles['data'] as $index => $dataString) {
               if (preg_match('/^data:(?<type>[a-z0-9]+\/[a-z0-9\+]+);base64,(?<data>.+)/i',
                   $dataString, $matches)) {
                   $type = $matches['type'];
                   $data = base64_decode($matches['data']);

                   if (!$data) {
                       continue;
                   }

                   if (!empty($this->_uploadedDataFiles['filename'][$index])) {
                       $filename = $this->_uploadedDataFiles['filename'][$index];
                   } else {
                       $extensions = FileHelper::getExtensionsByMimeType($type);

                       if (empty($extensions)) {
                           continue;
                       }

                       $filename = 'Uploaded_file.' . reset($extensions);
                   }

                   $uploadedFiles[] = [
                       'filename' => $filename,
                       'data' => $data,
                       'type' => 'data'
                   ];
               }
           }
       }

       // See if we have uploaded file(s).
       $paramName = $this->requestParamName($element);

       if ($paramName !== null) {
           $files = UploadedFile::getInstancesByName($paramName);

           foreach ($files as $file) {
               $uploadedFiles[] = [
                   'filename' => $file->name,
                   'location' => $file->tempName,
                   'type' => 'upload'
               ];
           }
       }

       return $uploadedFiles;
   }



 }
