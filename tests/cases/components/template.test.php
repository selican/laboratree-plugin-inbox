<?php
App::import('Controller', 'App');
App::import('Component', 'Template');

class TemplateComponentTestController extends AppController {
	var $name = 'Test';
	var $uses = array();
	var $components = array(
		'Template',
	);
}

class TemplateTest extends CakeTestCase
{
	var $fixtures = array('app.helps', 'app.app_category', 'app.app_data', 'app.application', 'app.app_module', 'app.attachment', 'app.digest', 'app.discussion', 'app.doc', 'app.docs_permission', 'app.docs_tag', 'app.docs_type_data', 'app.docs_type_field', 'app.docs_type', 'app.docs_type_row', 'app.docs_version', 'app.group', 'app.groups_address', 'app.groups_association', 'app.groups_award', 'app.groups_interest', 'app.groups_phone', 'app.groups_projects', 'app.groups_publication', 'app.groups_setting', 'app.groups_url', 'app.groups_users', 'app.inbox', 'app.inbox_hash', 'app.interest', 'app.message_archive', 'app.message', 'app.note', 'app.ontology_concept', 'app.preference', 'app.project', 'app.projects_association', 'app.projects_interest', 'app.projects_setting', 'app.projects_url', 'app.projects_users', 'app.role', 'app.setting', 'app.site_role', 'app.tag', 'app.type', 'app.url', 'app.user', 'app.users_address', 'app.users_association', 'app.users_award', 'app.users_education', 'app.users_interest', 'app.users_job', 'app.users_phone', 'app.users_preference', 'app.users_publication', 'app.users_url', 'app.ldap_user');

	function startTest()
	{
		$this->Controller = new TemplateComponentTestController();
		$this->Controller->constructClasses();
		$this->Controller->Component->initialize($this->Controller);

		$this->Controller->Session->write('Auth.User', array(
			'id' => 1,
			'name' => 'Test User',
			'username' => 'testuser',
			'changepass' => 0,
		));
	}

	function testTemplateInstance() {
		$this->assertTrue(is_a($this->Controller->Template, 'TemplateComponent'));
	}

	function testStartup() {
		$this->Controller->Template->startup(&$controller);
	}

	function testUserMessage()
	{
		$template = $this->Controller->Template->user_message();
		$expected = array(
			'name' => 'user_message',
			'data' => array(
				'sender' => $this->Controller->Session->read('Auth.User.name'),
				'sender_id' => $this->Controller->Session->read('Auth.User.id'),
			),
		);
		$this->assertEqual($template, $expected);
	}

	function testUserMessageInvalidSender()
	{
		$sender = 'invalid';

		try
		{
			$this->Controller->Template->user_message($sender);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testUserMessageValid() {
		$this->Controller->Template->user_message(array('name' => 'name', 'id' => 1));
	}

	function testGroupMessage()
	{
		$group_id = 1;

		$template = $this->Controller->Template->group_message($group_id);
		$expected = array(
			'name' => 'group_message',
			'data' => array(
				'sender' => $this->Controller->Session->read('Auth.User.name'),
				'sender_id' => $this->Controller->Session->read('Auth.User.id'),
				'group' => 'Private Test Group',
				'group_id' => $group_id,
			),
		);
		$this->assertEqual($template, $expected);
	}

	function testGroupMessageNullGroupId()
	{
		$group_id = null;

		try
		{
			$this->Controller->Template->group_message($group_id);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testGroupMessageInvalidGroupId()
	{
		$group_id = 'invalid';

		try
		{
			$this->Controller->Template->group_message($group_id);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testGroupMessageInvalidGroupIdNotFound()
	{
		$group_id = 9000;

		try
		{
			$this->Controller->Template->group_message($group_id);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testGroupMessageInvalidSender()
	{
		$group_id = 1;
		$sender = 'invalid';

		try
		{
			$this->Controller->Template->group_message($group_id, $sender);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testGroupMessageValid() {
                try {
                        $this->Controller->Session->write('Auth.User', array(
                                                                        'id' => 1,
                                                                        'name' => 'Test User',
                                                                        'username' => 'testuser',
                                                                        'changepass' => 0,
                                                                ));
                        $this->Controller->Template->group_message(1, array(
                                                                        'name' => 'Test User',
                                                                        'id' => 1,));
                }

                catch(InvalidArgumentException $e) {
                        $this->fail();
                }
	}

	function testProjectMessage()
	{
		$project_id = 1;

		$template = $this->Controller->Template->project_message($project_id);
		$expected = array(
			'name' => 'project_message',
			'data' => array(
				'sender' => $this->Controller->Session->read('Auth.User.name'),
				'sender_id' => $this->Controller->Session->read('Auth.User.id'),
				'project' => 'Private Test Project',
				'project_id' => $project_id,
			),
		);
		$this->assertEqual($template, $expected);
	}

	function testProjectMessageNullProjectId()
	{
		$project_id = null;

		try
		{
			$this->Controller->Template->project_message($project_id);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testProjectMessageInvalidProjectId()
	{
		$project_id = 'invalid';

		try
		{
			$this->Controller->Template->project_message($project_id);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testProjectMessageInvalidProjectIdNotFound()
	{
		$project_id = 9000;

		try
		{
			$this->Controller->Template->project_message($project_id);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testProjectMessageInvalidSender()
	{
		$project_id = 1;
		$sender = 'invalid';

		try
		{
			$this->Controller->Template->project_message($project_id, $sender);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	 function testProjectMessageValid() {
                try {
                        $this->Controller->Session->write('Auth.User', array(
                                                                        'id' => 1,
                                                                        'name' => 'Test User',
                                                                        'username' => 'testuser',
                                                                        'changepass' => 0,
                                                                ));
                        $this->Controller->Template->project_message(1, array(
                                                                        'name' => 'Test User',
                                                                        'id' => 1,));
                }

                catch(InvalidArgumentException $e) {
                        $this->fail();
                }
        }
/*
	function testGroupInvite()
	{
		$group_id = 1;

		$template = $this->Controller->Template->group_invite($group_id);
		$expected = array(
			'name' => 'group_invite',
			'data' => array(
				'sender' => $this->Controller->Session->read('Auth.User.name'),
				'sender_id' => $this->Controller->Session->read('Auth.User.id'),
				'group' => 'Private Test Group',
				'group_id' => $group_id,
			),
		);
		$this->assertEqual($template, $expected);
	}

	function testGroupInviteNullGroupId()
	{
		$group_id = null;

		try
		{
			$this->Controller->Template->group_invite($group_id);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testGroupInviteInvalidGroupId()
	{
		$group_id = 'invalid';

		try
		{
			$this->Controller->Template->group_invite($group_id);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testGroupInviteInvalidGroupIdNotFound()
	{
		$group_id = 9000;

		try
		{
			$this->Controller->Template->group_invite($group_id);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testGroupInviteInvalidSender()
	{
		$group_id = 1;
		$sender = 'invalid';

		try
		{
			$this->Controller->Template->group_invite($group_id, $sender);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	 function testGroupInviteValid() {
                try {
                        $this->Controller->Session->write('Auth.User', array(
                                                                        'id' => 1,
                                                                        'name' => 'Test User',
                                                                        'username' => 'testuser',
                                                                        'changepass' => 0,
                                                                ));
                        $this->Controller->Template->group_invite(1, array(
                                                                        'name' => 'Test User',
                                                                        'id' => 1,));
                }

                catch(InvalidArgumentException $e) {
                        $this->fail();
                }
        }

	function testProjectInvite()
	{
		$project_id = 1;

		$template = $this->Controller->Template->project_invite($project_id);
		$expected = array(
			'name' => 'project_invite',
			'data' => array(
				'sender' => $this->Controller->Session->read('Auth.User.name'),
				'sender_id' => $this->Controller->Session->read('Auth.User.id'),
				'project' => 'Private Test Project',
				'project_id' => $project_id,
			),
		);
		$this->assertEqual($template, $expected);
	}

	function testProjectInviteNullProjectId()
	{
		$project_id = null;

		try
		{
			$this->Controller->Template->project_invite($project_id);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testProjectInviteInvalidProjectId()
	{
		$project_id = 'invalid';

		try
		{
			$this->Controller->Template->project_invite($project_id);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testProjectInviteInvalidProjectIdNotFound()
	{
		$project_id = 9000;

		try
		{
			$this->Controller->Template->project_invite($project_id);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testProjectInviteInvalidSender()
	{
		$project_id = 1;
		$sender = 'invalid';

		try
		{
			$this->Controller->Template->project_invite($project_id, $sender);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	 function testProjectInviteValid() {
                try {
                        $this->Controller->Session->write('Auth.User', array(
                                                                        'id' => 1,
                                                                        'name' => 'Test User',
                                                                        'username' => 'testuser',
                                                                        'changepass' => 0,
                                                                ));
                        $this->Controller->Template->project_invite(1, array(
                                                                        'name' => 'Test User',
                                                                        'id' => 1,));
                }

                catch(InvalidArgumentException $e) {
                        $this->fail();
                }
        }
*/
	function testGroupAddEmptyGroupId() {
		try {
			$this->Controller->Template->group_add(null, array('data' => 'data'));
		}

		catch(InvalidArgumentException $e) {
			$this->pass();
		}
	}	

	function testGroupAddBoolGroupId() {
                try {
                        $this->Controller->Template->group_add(true, array(null));
                }

                catch(InvalidArgumentException $e) {
                        $this->pass();
                }
	}

	function testGroupAddStringGroupId() {
                try {
                        $this->Controller->Template->group_add('string', array(null));
                }

                catch(InvalidArgumentException $e) {
                        $this->pass();
                }
	}

	function testGroupAddNegativeGroupId() {
                try {
                        $this->Controller->Template->group_add(-1, array(null));
                }

                catch(InvalidArgumentException $e) {
                        $this->pass();
                }
	}

	function testGroupAddInvalidGroupId() {
                try {
                        $this->Controller->Template->group_add(10000000000000, array(null));
                }

                catch(InvalidArgumentException $e) {
                        $this->pass();
                }
	}

	function testGroupAddInvalidSenderArray() {
                try {
                        $this->Controller->Template->group_add(1, 'string');
                }

                catch(InvalidArgumentException $e) {
                        $this->pass();
                }
	}
	
	function testGroupAddValid() {
                try {
			$this->Controller->Session->write('Auth.User', array(
									'id' => 1,
									'name' => 'Test User',
									'username' => 'testuser',
									'changepass' => 0,
								));	
                        $this->Controller->Template->group_add(1, array(
									'name' => 'Test User',
									'id' => 1,));
                }

                catch(InvalidArgumentException $e) {
                        $this->fail();
                }
	}
        
	function testProjectAddEmptyProjectId() {
                try {
                        $this->Controller->Template->project_add(null, array('data' => 'data'));
                }

                catch(InvalidArgumentException $e) {
                        $this->pass();
                }
        }

        function testProjectAddBoolProjectId() {
                try {
                        $this->Controller->Template->project_add(true, array(null));
                }

                catch(InvalidArgumentException $e) {
                        $this->pass();
                }
        }

        function testProjectAddStringProjectId() {
                try {
                        $this->Controller->Template->project_add('string', array(null));
                }

                catch(InvalidArgumentException $e) {
                        $this->pass();
                }
        }

        function testProjectAddNegativeProjectId() {
                try {
                        $this->Controller->Template->project_add(-1, array(null));
                }

                catch(InvalidArgumentException $e) {
                        $this->pass();
                }
        }

        function testProjectAddInvalidProjectId() {
                try {
                        $this->Controller->Template->project_add(10000000000000, array(null));
                }

                catch(InvalidArgumentException $e) {
                        $this->pass();
                }
	}

        function testProjectAddInvalidSenderArray() {
                try {
                        $this->Controller->Template->project_add(1, 'string');
                }

                catch(InvalidArgumentException $e) {
                        $this->pass();
                }
	}

        function testProjectAddValid() {
                try {
                        $this->Controller->Session->write('Auth.User', array(
                                                                        'id' => 1,
                                                                        'name' => 'Test User',
                                                                        'username' => 'testuser',
                                                                        'changepass' => 0,
                                                                ));
                        $this->Controller->Template->project_add(1, array(
                                                                        'name' => 'Test User',
                                                                        'id' => 1,));
                }

                catch(InvalidArgumentException $e) {
                        $this->fail();
                }
        }

	function testGroupRequest()
	{
		$group_id = 1;

		$template = $this->Controller->Template->group_request($group_id);
		$expected = array(
			'name' => 'group_request',
			'data' => array(
				'sender' => $this->Controller->Session->read('Auth.User.name'),
				'sender_id' => $this->Controller->Session->read('Auth.User.id'),
				'group' => 'Private Test Group',
				'group_id' => $group_id,
			),
		);
		$this->assertEqual($template, $expected);
	}

	function testGroupRequestNullGroupId()
	{
		$group_id = null;

		try
		{
			$this->Controller->Template->group_request($group_id);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testGroupRequestInvalidGroupId()
	{
		$group_id = 'invalid';

		try
		{
			$this->Controller->Template->group_request($group_id);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testGroupRequestInvalidGroupIdNotFound()
	{
		$group_id = 9000;

		try
		{
			$this->Controller->Template->group_request($group_id);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testGroupRequestInvalidSender()
	{
		$group_id = 1;
		$sender = 'invalid';

		try
		{
			$this->Controller->Template->group_request($group_id, $sender);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testGroupRequestValid() {
		try {
			$this->Controller->Session->write('Auth.User', array(
									'id' => 1,
									'name' => 'Test User',
									'username' => 'testuser',
									'changepass' => 0,
								));
			$this->Controller->Template->group_request(1, array(
									'name' => 'Test User',
									'id' => 1,));
		}

		catch(InvalidArgumentException $e) {
			$this->fail();
		}
	}

	function testProjectRequest()
	{
		$project_id = 1;

		$template = $this->Controller->Template->project_request($project_id);
		$expected = array(
			'name' => 'project_request',
			'data' => array(
				'sender' => $this->Controller->Session->read('Auth.User.name'),
				'sender_id' => $this->Controller->Session->read('Auth.User.id'),
				'project' => 'Private Test Project',
				'project_id' => $project_id,
			),
		);
		$this->assertEqual($template, $expected);
	}

	function testProjectRequestNullGroupId()
	{
		$project_id = null;

		try
		{
			$this->Controller->Template->project_request($project_id);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testProjectRequestInvalidGroupId()
	{
		$project_id = 'invalid';

		try
		{
			$this->Controller->Template->project_request($project_id);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testProjectRequestInvalidGroupIdNotFound()
	{
		$project_id = 9000;

		try
		{
			$this->Controller->Template->project_request($project_id);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testProjectRequestInvalidSender()
	{
		$project_id = 1;
		$sender = 'invalid';

		try
		{
			$this->Controller->Template->project_request($project_id, $sender);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

        function testProjectRequestValid() {
                try {
                        $this->Controller->Session->write('Auth.User', array(
                                                                        'id' => 1,
                                                                        'name' => 'Test User',
                                                                        'username' => 'testuser',
                                                                        'changepass' => 0,
                                                                ));
                        $this->Controller->Template->project_request(1, array(
                                                                        'name' => 'Test User',
                                                                        'id' => 1,));
                }

                catch(InvalidArgumentException $e) {
                        $this->fail();
                }
        }

	function testNewUserEmptyUsername() {
		try {
			$this->Controller->Template->new_user(null, 'string', 1, 'string', array(null));
			$this->fail();
		}

		catch(InvalidArgumentException $e) {
			$this->pass();
		}
	}

	function testNewUserBoolUsername() {
                try {
                        $this->Controller->Template->new_user(true, 'string', 1, 'string', array(null));
                        $this->fail();
                }

                catch(InvalidArgumentException $e) {
                        $this->pass();
                }
        }

	function testNewUserIntUsername() {
                try {
                        $this->Controller->Template->new_user(1, 'string', 1, 'string', array(null));
                        $this->fail();
                }

                catch(InvalidArgumentException $e) {
                        $this->pass();
                }
        }

	function testNewUserEmptyPassword() {
                try {
                        $this->Controller->Template->new_user('string', null, 1, 'string', array(null));
                        $this->fail();
                }

                catch(InvalidArgumentException $e) {
                        $this->pass();
                }
        }

	function testNewUserBoolPassword() {
                try {
                        $this->Controller->Template->new_user('string', true, 1, 'string', array(null));
                        $this->fail();
                }

                catch(InvalidArgumentException $e) {
                        $this->pass();
                }
        }

	function testNewUserIntPassword() {
                try {
                        $this->Controller->Template->new_user('string', 1, 1, 'string', array(null));
                        $this->fail();
                }

                catch(InvalidArgumentException $e) {
                        $this->pass();
                }
        }

	function testNewUserEmptyUserId() {
                try {
                        $this->Controller->Template->new_user('string', 'string', null, 'string', array(null));
                        $this->fail();
                }

                catch(InvalidArgumentException $e) {
                        $this->pass();
                }
        }

	function testNewUserBoolUserId() {
                try {
                        $this->Controller->Template->new_user('string', 'string', true, 'string', array(null));
                        $this->fail();
                }

                catch(InvalidArgumentException $e) {
                        $this->pass();
                }
        }
	
	function testNewUserStringUserId() {
                try {
                        $this->Controller->Template->new_user('string', 'string', 'string', 'string', array(null));
                        $this->fail();
                }

                catch(InvalidArgumentException $e) {
                        $this->pass();
                }
        }

	function testNewUserEmptyHash() {
                try {
                        $this->Controller->Template->new_user('string', 'string', 1, null, array(null));
                        $this->fail();
                }

                catch(InvalidArgumentException $e) {
                        $this->pass();
                }
        }

	function testNewUserBoolHash() {
                try {
                        $this->Controller->Template->new_user('string', 'string', 1, true, array(null));
                        $this->fail();
                }

                catch(InvalidArgumentException $e) {
                        $this->pass();
                }
        }

	function testNewUserIntHash() {
                try {
                        $this->Controller->Template->new_user('string', 'string', 1, 1, array(null));
                        $this->fail();
                }

                catch(InvalidArgumentException $e) {
                        $this->pass();
                }
        }

	function testNewUserBoolSender() {
                try {
                        $this->Controller->Template->new_user('string', 'string', 1, 'string', true);
                        $this->fail();
                }

                catch(InvalidArgumentException $e) {
                        $this->pass();
                }
        }

	function testNewUserIntSender() {
                try {
                        $this->Controller->Template->new_user('string', 'string', 1, 'string', 1);
                        $this->fail();
                }

                catch(InvalidArgumentException $e) {
                        $this->pass();
                }
        }
	function testNewUserValid() {
                        $this->Controller->Session->write('Auth.User', array(
                                                                        'id' => 1,
                                                                        'name' => 'Test User',
                                                                        'username' => 'testuser',
                                                                        'changepass' => 0,
                                                                ));
                        $this->Controller->Template->new_user('Test User', 'testuser', 1, 'HASHHASHHASH', array(
                                                                        'name' => 'Test User',
                                                                        'id' => 1,));
	}


	function testBuild()
	{
		$name = 'build_test';
		$data = array(
			'data' => 'test',
		);

		$template = $this->Controller->Template->build($name, $data);
		$expected = array(
			'name' => $name,
			'data' => $data,
		);
		$this->assertEqual($template, $expected);
	}

	function testBuildNullName()
	{
		$name = null;
		$data = array(
			'data' => 'test',
		);

		try
		{
			$this->Controller->Template->build($name, $data);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testBuildInvalidName()
	{
		$name = array(
			'invalid' => 'invalid',
		);
		$data = array(
			'data' => 'test',
		);

		try
		{
			$this->Controller->Template->build($name, $data);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testBuildNullData()
	{
		$name = 'build_test';
		$data = null;

		try
		{
			$this->Controller->Template->build($name, $data);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testBuildInvalidData()
	{
		$name = 'build_test';
		$data = 'invalid';

		try
		{
			$this->Controller->Template->build($name, $data);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testBuildInvalidSender()
	{
		$name = 'build_test';
		$data = array(
			'data' => 'test',
		);
		$sender = 'invalid';

		try
		{
			$this->Controller->Template->build($name, $data, $sender);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

        function testBuildValid() {
                try {
                        $this->Controller->Session->write('Auth.User', array(
                                                                        'id' => 1,
                                                                        'name' => 'Test User',
                                                                        'username' => 'testuser',
                                                                        'changepass' => 0,
                                                                ));
                        $this->Controller->Template->build('ethan', array('data' => 'stuff'), array(
                                                                        'name' => 'Test User',
                                                                        'id' => 1,));
                }

                catch(InvalidArgumentException $e) {
                        $this->fail();
                }
        }

	function testSender() {
                        $this->Controller->Session->write('Auth.User', array(
                                                                        'id' => 1,
                                                                        'name' => 'Test User',
                                                                        'username' => 'testuser',
                                                                        'changepass' => 0,
                                                                ));
			$this->Controller->Template->sender();
	}

	function endTest() {
		unset($this->Controller);
		ClassRegistry::flush();	
	}
}
?>
