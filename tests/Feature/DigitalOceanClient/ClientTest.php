<?php

namespace Tests\Feature\DigitalOceanClient;
use Tests\TestCase;

use Akromjon\DigitalOceanClient\DigitalOceanClient;

class ClientTest extends TestCase
{
    protected DigitalOceanClient $digitalOceanClient;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->token=env('DIGITAL_OCEAN_TOKEN');

        $this->digitalOceanClient=new DigitalOceanClient($this->token);

    }
    public function test_it_can_get_projects()
    {
        $base = $this->digitalOceanClient;

        $this->assertIsArray($base->projects());
    }

    public function test_it_can_get_default_and_single_projects()
    {
        $base = $this->digitalOceanClient;

        $defaultProject=$base->defaultProject();

        $this->assertTrue($defaultProject['is_default']);

        $this->assertIsArray($base->project($defaultProject['id']));
    }

    public function test_it_can_add_a_new_project()
    {
        $base = $this->digitalOceanClient;

        $name='test_project_'.rand(1,1000);

        $project=$base->createProject($name);

        $this->assertEquals($name,$project['name']);
    }

    public function test_it_can_update_a_project()
    {
        $base = $this->digitalOceanClient;

        $name='test_project_'.rand(1,1000);

        $project=$base->createProject($name);

        $this->assertEquals($name,$project['name']);

        $newName='test_project_'.rand(1,1000);

        $updatedProject=$base->updateProject($project['id'],$newName);

        $this->assertEquals($newName,$updatedProject['name']);
    }

    public function test_it_can_delete_all_test_projects()
    {
        $base = $this->digitalOceanClient;

        $name='test_project_'.rand(1,1000);

        $base->createProject($name);

        $projects=$base->projects();

        foreach ($projects as $project)
        {
            if(str_contains($project['name'],'test_project_'))
            {
                $base->deleteProject($project['id']);
            }
        }

        $projects=$base->projects();

        foreach ($projects as $project)
        {
            $this->assertStringNotContainsString('test_project_',$project['name']);
        }

        $this->assertCount(2,$projects);
    }

    public function test_it_can_create_project_and_set_it_default()
    {
        $base = $this->digitalOceanClient;

        $name='im-test-default-project'.rand(1,1000);

        $project=$base->createProject($name);

        $this->assertEquals($name,$project['name']);

        $updatedProject=$base->updateProject(
            projectId: $project['id'],
            isDefault: true
        );

        $this->assertTrue($updatedProject['is_default']);
    }

    public function test_it_can_update_with_same_name()
    {
        $base = $this->digitalOceanClient;

        $project=$base->defaultProject();

        $name='im-test-default-project34';

        $updatedProject=$base->updateProject(
            projectId: $project['id'],
            name: $name
        );

        $this->assertEquals($name,$updatedProject['name']);
    }

    public function test_it_can_set_staging_project_to_default_and_delete_all_test_projects()
    {
        $base=$this->digitalOceanClient;

        $projects=$base->projects();

        foreach ($projects as $project)
        {
            if($project['environment']=='Staging')
            {
                $updatedProject=$base->updateProject(
                    projectId: $project['id'],
                    isDefault: true
                );

                $this->assertTrue($updatedProject['is_default']);
            }
        }

        foreach ($projects as $project)
        {
            if(str_contains($project['name'],'test'))
            {
                $base->deleteProject($project['id']);
            }
        }

        $projects=$base->projects();

        foreach ($projects as $project)
        {
            $this->assertStringNotContainsString('test',$project['name']);
        }

    }




}
