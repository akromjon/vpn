<?php

namespace Akromjon\Pritunl;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PharData;




class VPNConfig
{
    const path="servers/vpnconfigs/";

    protected string $organizationId;

    protected string $userId;

    protected string $ip;


    public function __construct(string $ip,string $organizationId,string $userId)
    {
        $this->ip=$ip;
        $this->organizationId=$organizationId;
        $this->userId=$userId;
    }

    public function baseBath():string
    {

        $path=storage_path(self::path);

        if(!file_exists($path))
        {
            File::makeDirectory($path);
        }

        return $path;
    }

    public function filePath():string{
        return "{$this->ip}/{$this->organizationId}/{$this->userId}";
    }

    public function download(string $body):string
    {
        $file=$this->filePath().".tar";

        $this->putToStorage($file,$body);

        $this->extractTarFile($file);

        $this->deleteFile($file);

       return $this->getExtractedFile();
    }

    private function putToStorage(string $file,string $content):void
    {
        Storage::disk('servers')->put($file,$content);
    }

    private function extractTarFile(string $file):void
    {
        $zip = new PharData($this->baseBath().$file);

        $zip->extractTo($this->baseBath().$this->filePath());
    }

    private function deleteFile(string $file):void
    {
        Storage::disk('servers')->delete($file);
    }
    public function getExtractedFile(): string
    {
        $file = $this->baseBath() . $this->filePath() . "/" . $this->lookForFile();

        $newFile = $this->baseBath() . $this->filePath() . "/config.ovpn";

        rename($file, $newFile);

        $fileContent = File::get($newFile);

        $cleanedContent = strstr($fileContent, '</key>', true)."</key>";

        File::put($newFile, $cleanedContent);

        return $newFile;
    }

    private function lookForFile():string
    {
        $files = scandir($this->baseBath().$this->filePath());

        $files = array_diff($files, ['.', '..']);

        return reset($files);
    }


}
