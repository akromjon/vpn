<?php

namespace Akromjon\DigitalOceanClient;
use Akromjon\DigitalOceanClient\Base;
use Illuminate\Support\Facades\Cache;

class DigitalOceanClient extends Base
{
    public function projects(): array
    {
        $projects=Cache::rememberForever('digitalocean.projects', function ()  {
            return $this->baseHTTP('get', 'projects')->json('projects');
        });

        return $this->wrapInArray($projects);
    }

    public function project(string $projectId): array
    {
        $response = $this->baseHTTP('get', 'projects/' . $projectId);

        return $this->wrapInArray($response->json('project'));
    }

    public function projectResources(string $projectId): array
    {
        $response = $this->baseHTTP('get', 'projects/' . $projectId . '/resources');

        return $this->wrapInArray($response->json('resources'));
    }

    public function assignProjectResources(string $projectId, array $resources): array
    {
        $response = $this->baseHTTP('post', 'projects/' . $projectId . '/resources', [
            'resources' => $resources
        ]);

        return $this->wrapInArray($response->json('resources'));
    }

    public function assignProjectDroplets(string $projectId, array $dropletIds): array
    {
        $resources=[];

        array_map(function ($dropletId) use (&$resources) {
            $resources[] = "do:droplet:$dropletId";
        }, $dropletIds);

        return $this->assignProjectResources($projectId, $resources);
    }

    public function createProject(
        string $name,
        string $purpose="",
        string $description="",
        string $environment=""): array
    {

        $response = $this->baseHTTP('post', 'projects', [
            'name' => $name,
            'purpose' => $this->getValueOrDefault($purpose, $this->getProjectPurposes()[4]),
            'description' => $this->getValueOrDefault($description, $this->getProjectPurposes()[4]),
            'environment' => $this->getValueOrDefault($environment, "Development")
        ]);

        return $this->wrapInArray($response->json('project'));
    }

    public function updateProject(string $projectId, string $name = "", string $purpose = "", string $description = "", string $environment = "", bool $isDefault = false): array
    {
        $project = $this->project($projectId);

        $params = [
            'name' => $this->getValueOrDefault($name, $project['name']),
            'purpose' => $this->getValueOrDefault($purpose, $project['purpose']),
            'description' => $this->getValueOrDefault($description, $project['description']),
            'environment' => $this->getValueOrDefault($environment, $project['environment']),
            'is_default' => $this->getValueOrDefault($isDefault, $project['is_default']),
        ];

        $response = $this->baseHTTP('put', 'projects/' . $projectId, $params);

        return $this->wrapInArray($response->json('project'));
    }

    public function deleteProject(string $projectId): array
    {

        $response = $this->baseHTTP('delete', 'projects/' . $projectId, [
            'project_id' => $projectId
        ]);

        return $this->wrapInArray($response->json('project'));
    }

    public function defaultProject(): array
    {
        $response = $this->baseHTTP('get', 'projects/default');

        return $this->wrapInArray($response->json('project'));
    }

    public function sizes(): array
    {
        $sizes=Cache::rememberForever('digitalocean.sizes', function ()  {
            return $this->baseHTTP('get', 'sizes')->json('sizes');
        });

        return $this->wrapInArray($sizes);
    }

    public function snapshots(string $resourceType = "droplet"): array
    {
        $snapshots=Cache::rememberForever("digitalocean.snapshots.{$resourceType}", function () use ($resourceType) {
            return $this->baseHTTP('get', 'snapshots', [
                'resource_type' => $resourceType
            ])->json('snapshots');
        });

        return $this->wrapInArray($snapshots);
    }

    public function snapshot(string $snapshotId): array
    {
        $response = $this->baseHTTP('get', 'snapshots/' . $snapshotId);

        return $this->wrapInArray($response->json('snapshot'));
    }

    public function vpcs():array
    {
        $response = $this->baseHTTP('get', 'vpcs');

        return $this->wrapInArray($response->json('vpcs'));
    }

    public function vpc(string $vpcId):array
    {
        $response = $this->baseHTTP('get', 'vpcs/' . $vpcId);

        return $this->wrapInArray($response->json('vpc'));
    }

    public function account():array
    {
        $response = $this->baseHTTP('get', 'account');

        return $this->wrapInArray($response->json('account'));
    }

    public function images(string $private="false",string $type="distribution"):array
    {
        $response = $this->baseHTTP('get', 'images',[
            'private'=>$private,
            'type'=>$type,
        ]);

        return $this->wrapInArray($response->json('images'));
    }

    public function image(string $imageId):array
    {
        $response = $this->baseHTTP('get', 'images/' . $imageId);

        return $this->wrapInArray($response->json('image'));
    }

    public function regions():array
    {
        $regions=Cache::rememberForever('digitalocean.regions', function ()  {
            return $this->baseHTTP('get', 'regions')->json('regions');
        });

        return $this->wrapInArray($regions);
    }

    public function sshKeys():array
    {
        $keys=Cache::rememberForever('digitalocean.ssh_keys', function ()  {
            return $this->baseHTTP('get', 'account/keys')->json('ssh_keys');
        });

        return $this->wrapInArray($keys);
    }

    public function sshKey(string $sshKeyId):array
    {
        $response = $this->baseHTTP('get', 'account/keys/'.$sshKeyId);

        return $this->wrapInArray($response->json('ssh_key'));
    }

    public function createSshKey(string $name,string $publicKey):array
    {
        $response = $this->baseHTTP('post', 'account/keys',[
            'name'=>$name,
            'public_key'=>$publicKey
        ]);

        return $this->wrapInArray($response->json('ssh_key'));
    }

    public function deleteSshKey(string $sshKeyId):array
    {
        $response = $this->baseHTTP('delete', 'account/keys/'.$sshKeyId);

        return $this->wrapInArray($response->json('ssh_key'));
    }

    public function updateSshKey(string $sshKeyId,string $name):array
    {
        $response = $this->baseHTTP('put', 'account/keys/'.$sshKeyId,[
            'name'=>$name,
        ]);

        return $this->wrapInArray($response->json('ssh_key'));
    }

    public function deteleteSshKey(string $sshKeyId):array
    {
        $response = $this->baseHTTP('delete', 'account/keys/'.$sshKeyId);

        return $this->wrapInArray($response->json('ssh_key'));
    }

    public function droplets():array
    {
        $response=$this->baseHTTP('get','droplets');

        return $this->wrapInArray($response->json('droplets'));
    }

    public function droplet(string $dropletId):array
    {
        $response=$this->baseHTTP('get','droplets/'.$dropletId);

        return $this->wrapInArray($response->json('droplet'));
    }

    public function createDroplet(string $name,string $regionSlug,string $sizeSlug,string $imageIdOrSlug,string $projectId="",array $sshKeyIds=[],bool $backups=false, bool $ipv6=false,bool $monitoring=false,array $tags=[],string $vpcUuid="",):array
    {
        $response=$this->baseHTTP('post','droplets',[
            'name'=>$name,
            'region'=>$regionSlug,
            'size'=>$sizeSlug,
            'image'=>$imageIdOrSlug,
            'ssh_keys'=>$sshKeyIds,
            'backups'=>$backups,
            'ipv6'=>$ipv6,
            'monitoring'=>$monitoring,
            'tags'=>$tags,
            'vpc_uuid'=>$vpcUuid,
        ]);

        if(!empty($projectId)){
            $this->assignProjectDroplets($projectId,[$response->json('droplet')['id']]);
        }

        return $this->wrapInArray($response->json('droplet'));
    }

    public function deleteDroplet(string $dropletId):array
    {
        $response=$this->baseHTTP('delete','droplets/'.$dropletId);

        return $this->wrapInArray($response->json('droplet'));
    }

}
