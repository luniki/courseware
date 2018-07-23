<?php
namespace Mooc\UI\BeforeAfterBlock;

use Mooc\UI\Block;

class BeforeAfterBlock extends Block 
{
    const NAME = 'Bildvergleich';
    const BLOCK_CLASS = 'multimedia';
    const DESCRIPTION = 'Vergleicht zwei Bilder mit einem Schieberegler';

    public function initialize()
    {
        $this->defineField('ba_source', \Mooc\SCOPE_BLOCK, '');
        $this->defineField('ba_url', \Mooc\SCOPE_BLOCK, '');
        $this->defineField('ba_files', \Mooc\SCOPE_BLOCK, '');
    }

    public function student_view()
    {   
        $this->setGrade(1.0);

        return array_merge($this->getAttrArray(), array());
    }

    public function author_view()
    {
        $this->authorizeUpdate();
        
        

        return array_merge($this->getAttrArray(), array(

            'image_files' => $this->showFiles()
        ));
    }

    private function getAttrArray() 
    {
        switch ($this->ba_source) {
                case 'url':
                    $url_json = json_decode($this->ba_url);
                    $ba_img_before = $url_json->before->url;
                    $ba_img_after = $url_json->after->url;
                    break;
                case 'cw':
                    $file_json = json_decode($this->ba_files);
                    $file_before = \FileRef::find($file_json->before->file_id);
                    $file_after = \FileRef::find($file_json->after->file_id);
                    if (($file_before)&&($file_after)) {
                        $ba_img_before = $file_before->getDownloadURL();
                        $ba_img_after = $file_after->getDownloadURL();
                    }
                    break;
        }

        return array(
            'ba_source' => $this->ba_source,
            'ba_url' => $this->ba_url,
            'ba_files' => $this->ba_files,
            'beforeafter_img_before' => $ba_img_before,
            'beforeafter_img_after' => $ba_img_after
        );
    }

    public function save_handler(array $data)
    {
        $this->authorizeUpdate();

        if (isset ($data['ba_source'])) {
            $this->ba_source = (string) $data['ba_source'];
        } 
        if (isset ($data['ba_url'])) {
            $this->ba_url = (string) $data['ba_url'];
        } 
        if (isset ($data['ba_files'])) {
            $this->ba_files = (string) $data['ba_files'];
        } 

        return;
    }

    public function exportProperties()
    { 
       return array(
            'ba_source' => $this->ba_source,
            'ba_url' => $this->ba_url,
            'ba_files' => $this->ba_files
            );
    }

    public function getFiles()
    {
        if ($this->ba_source != 'cw') {
            return array();
        }
        $file_json = json_decode($this->ba_files);

        foreach ($file_json as $ba_file){
            $file_ref = new \FileRef($ba_file->file_id);
            $file = new \File($file_ref->file_id);

            $files[] = array(
                'id' => $ba_file->file_id,
                'name' => $file_ref->name,
                'description' => $file_ref->description,
                'filename' => $file->name,
                'filesize' => $file->size,
                'url' => $file->getURL(),
                'path' => $file->getPath()
            );
        }

        return $files;
    }

    public function getXmlNamespace()
    {
        return 'http://moocip.de/schema/block/beforeafter/';
    }

    public function getXmlSchemaLocation()
    {
        return 'http://moocip.de/schema/block/beforeafter/beforeafter-1.0.xsd';
    }

    public function importProperties(array $properties)
    {
        if (isset($properties['ba_source'])) {
            $this->ba_source = $properties['ba_source'];
        }
        if (isset($properties['ba_url'])) {
            $this->ba_url = $properties['ba_url'];
        }
        if (isset($properties['ba_files'])) {
            $this->ba_files = $properties['ba_files'];
        }

        $this->save();
    }

    public function importContents($contents, array $files)
    {
        $file_json = json_decode($this->ba_files);

        foreach($files as $file){
            if($file_json->after->file_name == $file->name) {
                $file_json->after->file_id = $file->id;
            }
            if($file_json->before->file_name == $file->name) {
                $file_json->before->file_id = $file->id;
            }
        }
        $this->ba_files = json_encode($file_json);

        $this->save();
    }

    private function showFiles()
    {
        $filesarray = array();
        $folders =  \Folder::findBySQL('range_id = ?', array($this->container['cid']));
        
        foreach ($folders as $folder) {
            $file_refs = \FileRef::findBySQL('folder_id = ?', array($folder->id));
            foreach($file_refs as $ref){
                if ($ref->isImage())  {
                    $filesarray[] = $ref;
                }
            }
        }

        return $filesarray;
    }
}
