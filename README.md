## DigitalOceanClient Class

The `DigitalOceanClient` class is a part of our application designed to interact with the DigitalOcean API. It provides a convenient way to perform various operations on DigitalOcean resources.
### Examples:
Creating an instance of DigitalOceanClient Class.
```php
$client =DigitalOceanClient::connect($apiToken);
```
###### Projects - Supports CRUD actions and Project Resources: getting default project, assigning project resources and droplets as well.
```php
$client->projects():array;
$client->createProject(
    string $name,
    string $purpose="",
    string $description="",
    string $environment=""
):array;
$client->project(string $projectId):array;
$client->updateProject(
    string $projectId, 
    string $name = "", 
    string $purpose = "", 
    string $description = "", 
    string $environment = "", 
    bool $isDefault = false
):array;
$client->deleteProject(string $projectId):array;

$client->projectResources(string $projectId):array;

$client->assignProjectResources(
string $projectId, 
array $resources,
):array;

$client->assignProjectDroplets(
string $projectId, 
array $dropletIds,
);

$client->defaultProject():array;
```


