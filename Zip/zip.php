<?php

class Zip
{
    private $zip;
    private $zipName = '';

    public function __construct(string $zipName)
    {
        $this->checkIfZipModuleIsLoaded();
        $this->checkZipExtensionInZipName($zipName);
        $this->zipName = $zipName;
        $this->zip = new ZipArchive();
        $this->zip->open($this->zipName, ZipArchive::CREATE);
    }

    private function checkIfZipModuleIsLoaded()
    {
        if (!extension_loaded('zip')) {
            throw new Exception('No está cargado el módulo de ficheros zip');
        }
    }

    private function checkZipExtensionInZipName($zipName)
    {
        if (strpos($zipName, 'zip') === false) {
            throw new Exception('El nombre del fichero tiene que tener extension .zip');
        }
    }

    public function addFile(string $fileName)
    {
        $this->zip->addFile($fileName, $fileName);
    }

    public function addFiles(array $arrFileNames)
    {
        $notExistingFiles = '';
        foreach ($arrFileNames as $fileName) {
            if (file_exists($fileName)) {
                $this->addFile($fileName);
            } else {
                $notExistingFiles .= $fileName.',';
            }
        }
        $fileName = rtrim($fileName, ',');
        if (str_word_count($fileName) > 0) {
            throw new Exception('Los siguientes ficheros no existen: '.$notExistingFiles.' y no se han comprimido');
        }
    }

    public function addFilesByFolder($folder)
    {
        if (is_dir($folder)) {
            if (substr($folder, -1) !== '/') {
                $folder .= '/';
            }
            $this->zip->addEmptyDir($folder);
            $list = glob("$folder*.*");
            foreach ($list as $fileName) {
                $this->addFile($fileName);
            }
        } else {
            throw new Exception('No existe la ruta.');
        }
    }

    public function extractTo($extractPath)
    {
        if ($this->zip->open($this->zipName) === true) {
            if (!is_dir($extractPath)) {
                mkdir($extractPath, 0755, true);
            }
            $this->zip->extractTo($extractPath);
        }
    }

    public function __destruct()
    {
        $this->zip->close();
    }
}
