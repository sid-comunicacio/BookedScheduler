<?php
/**
 * Copyright 2011-2018 Nick Korbel
 *
 * This file is part of Booked Scheduler.
 *
 * Booked Scheduler is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Booked Scheduler is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Booked Scheduler.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once(ROOT_DIR . 'Domain/Access/namespace.php');
require_once(ROOT_DIR . 'Presenters/ActionPresenter.php');
require_once(ROOT_DIR . 'lib/Application/Authentication/namespace.php');
require_once(ROOT_DIR . 'lib/Application/User/namespace.php');
require_once(ROOT_DIR . 'lib/Application/Admin/UserImportCsv.php');
require_once(ROOT_DIR . 'lib/Application/Admin/CsvImportResult.php');
require_once(ROOT_DIR . 'lib/Application/Admin/ProductorImportResult.php');
require_once(ROOT_DIR . 'lib/Email/Messages/InviteUserEmail.php');
require_once(ROOT_DIR . 'lib/Email/Messages/AccountCreationForUserEmail.php');

class ManageUsersActions
{
    const Activate = 'activate';
    const AddUser = 'addUser';
    const ChangeAttribute = 'changeAttribute';
    const Deactivate = 'deactivate';
    const DeleteUser = 'deleteUser';
    const Password = 'password';
    const Permissions = 'permissions';
    const UpdateUser = 'updateUser';
    const ChangeColor = 'changeColor';
    const ImportUsers = 'importUsers';
    const ChangeCredits = 'changeCredits';
    const InviteUsers = 'inviteUsers';
    const DeleteMultipleUsers = 'deleteMultipleUsers';
    const DoSanction = 'doSanction'; //santosm
    const ImportProductors = 'importProductors';//santosm
}

interface IManageUsersPresenter
{
    public function AddUser();

    public function UpdateUser();
}

class ManageUsersPresenter extends ActionPresenter implements IManageUsersPresenter
{
    /**
     * @var IManageUsersPage
     */
    private $page;

    /**
     * @var IUserRepository
     */
    private $userRepository;

    /**
     * @var ResourceRepository
     */
    private $resourceRepository;

    /**
     * @var PasswordEncryption
     */
    private $passwordEncryption;

    /**
     * @var IManageUsersService
     */
    private $manageUsersService;

    /**
     * @var IAttributeService
     */
    private $attributeService;

    /**
     * @var IGroupRepository
     */
    private $groupRepository;

    /**
     * @var IGroupViewRepository
     */
    private $groupViewRepository;

    /**
     * @param IGroupRepository $groupRepository
     */
    public function SetGroupRepository($groupRepository)
    {
        $this->groupRepository = $groupRepository;
    }

    /**
     * @param IGroupViewRepository $groupViewRepository
     */
    public function SetGroupViewRepository($groupViewRepository)
    {
        $this->groupViewRepository = $groupViewRepository;
    }

    /**
     * @param IAttributeService $attributeService
     */
    public function SetAttributeService($attributeService)
    {
        $this->attributeService = $attributeService;
    }

    /**
     * @param IManageUsersService $manageUsersService
     */
    public function SetManageUsersService($manageUsersService)
    {
        $this->manageUsersService = $manageUsersService;
    }

    /**
     * @param ResourceRepository $resourceRepository
     */
    public function SetResourceRepository($resourceRepository)
    {
        $this->resourceRepository = $resourceRepository;
    }

    /**
     * @param UserRepository $userRepository
     */
    public function SetUserRepository($userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param IManageUsersPage $page
     * @param UserRepository $userRepository
     * @param IResourceRepository $resourceRepository
     * @param PasswordEncryption $passwordEncryption
     * @param IManageUsersService $manageUsersService
     * @param IAttributeService $attributeService
     * @param IGroupRepository $groupRepository
     * @param IGroupViewRepository $groupViewRepository
     */
    public function __construct(IManageUsersPage $page,
                                UserRepository $userRepository,
                                IResourceRepository $resourceRepository,
                                PasswordEncryption $passwordEncryption,
                                IManageUsersService $manageUsersService,
                                IAttributeService $attributeService,
                                IGroupRepository $groupRepository,
                                IGroupViewRepository $groupViewRepository)
    {
        parent::__construct($page);

        $this->page = $page;
        $this->userRepository = $userRepository;
        $this->resourceRepository = $resourceRepository;
        $this->passwordEncryption = $passwordEncryption;
        $this->manageUsersService = $manageUsersService;
        $this->attributeService = $attributeService;
        $this->groupRepository = $groupRepository;
        $this->groupViewRepository = $groupViewRepository;

        $this->AddAction(ManageUsersActions::Activate, 'Activate');
        $this->AddAction(ManageUsersActions::AddUser, 'AddUser');
        $this->AddAction(ManageUsersActions::Deactivate, 'Deactivate');
        $this->AddAction(ManageUsersActions::DeleteUser, 'DeleteUser');
        $this->AddAction(ManageUsersActions::Password, 'ResetPassword');
        $this->AddAction(ManageUsersActions::Permissions, 'ChangePermissions');
        $this->AddAction(ManageUsersActions::UpdateUser, 'UpdateUser');
        $this->AddAction(ManageUsersActions::ChangeAttribute, 'ChangeAttribute');
        $this->AddAction(ManageUsersActions::ChangeColor, 'ChangeColor');
        $this->AddAction(ManageUsersActions::ImportUsers, 'ImportUsers');
        $this->AddAction(ManageUsersActions::ChangeCredits, 'ChangeCredits');
        $this->AddAction(ManageUsersActions::InviteUsers, 'InviteUsers');
        $this->AddAction(ManageUsersActions::DeleteMultipleUsers, 'DeleteMultipleUsers');
        $this->AddAction(ManageUsersActions::DoSanction, 'DoSanction');//santosm
        $this->AddAction(ManageUsersActions::ImportProductors, 'ImportProductors');//santosm
    }

    //santosm
    public function DoSanction() {
        $user = $this->userRepository->LoadById($this->page->GetUserId());
        $user->ChangeSanction($this->page->GetSanctionStart(), $this->page->GetSanctionEnd());
        $this->userRepository->Update($user);
        /*$this->manageUsersService->UpdateUser($this->page->GetUserId(),
        $this->page->GetUserName(),
        $this->page->GetEmail(),
        $this->page->GetFirstName(),
        $this->page->GetLastName(),
        $this->page->GetTimezone(),
        $extraAttributes);*/
    }
    //santosm START
    public function PageLoad($userTimezone)
    {
		$startDateString = $this->page->GetSanctionStart();
		$endDateString = $this->page->GetSanctionEnd();

		$startDate = $this->GetDate($startDateString, $userTimezone, 0);
		$endDate = $this->GetDate($endDateString, $userTimezone, 14);

        $this->page->SetSanctionStart($startDate);
        $this->page->SetSanctionEnd($endDate);

        $this->page->SetNewValidityStart($startDate);
        $this->page->SetNewValidityEnd($endDate);
    //santosm END
        if ($this->page->GetUserId() != null) {
            $userList = $this->userRepository->GetList(1, 1, null, null,
                new SqlFilterEquals(ColumnNames::USER_ID, $this->page->GetUserId()));
        }
        else {
            $userList = $this->userRepository->GetList($this->page->GetPageNumber(), $this->page->GetPageSize(),
                $this->page->GetSortField(),
                $this->page->GetSortDirection(), null, $this->page->GetFilterStatusId());
        }

        $this->page->BindUsers($userList->Results());
        $this->page->BindPageInfo($userList->PageInfo());

        $groups = $this->groupViewRepository->GetList();
        $this->page->BindGroups($groups->Results());

        $user = $this->userRepository->LoadById(ServiceLocator::GetServer()->GetUserSession()->UserId);

        $resources = $this->GetResourcesThatCurrentUserCanAdminister($user);
        $this->page->BindResources($resources);

        $attributeList = $this->attributeService->GetByCategory(CustomAttributeCategory::USER);
        $this->page->BindAttributeList($attributeList);

		$this->page->BindStatusDescriptions();
    }
    //santosm taken from manageblackout presenter
    private function GetDate($dateString, $timezone, $defaultDays)
	{
		$date = null;
		if (is_null($dateString))
		{
			$date = Date::Now()->AddDays($defaultDays)->ToTimezone($timezone)->GetDate();

		}
		elseif (!empty($dateString))
		{
			$date = Date::Parse($dateString, $timezone);
		}

		return $date;
	}
    //santosm END

    public function Deactivate()
    {
        $user = $this->userRepository->LoadById($this->page->GetUserId());
        $user->Deactivate();
        $this->userRepository->Update($user);
        $this->page->SetJsonResponse(Resources::GetInstance()->GetString('Inactive'));
    }

    public function Activate()
    {
        $user = $this->userRepository->LoadById($this->page->GetUserId());
        $user->Activate();
        $this->userRepository->Update($user);
        $this->page->SetJsonResponse(Resources::GetInstance()->GetString('Active'));
    }

    public function AddUser()
    {
        $defaultHomePageId = Configuration::Instance()->GetKey(ConfigKeys::DEFAULT_HOMEPAGE, new IntConverter());
        $extraAttributes = array(
            UserAttribute::Organization => $this->page->GetOrganization(),
            UserAttribute::Phone => $this->page->GetPhone(),
            UserAttribute::Position => $this->page->GetPosition());

        $user = $this->manageUsersService->AddUser(
            $this->page->GetUserName(),
            $this->page->GetEmail(),
            $this->page->GetFirstName(),
            $this->page->GetLastName(),
            $this->page->GetPassword(),
            $this->page->GetTimezone(),
            Configuration::Instance()->GetKey(ConfigKeys::LANGUAGE),
            empty($defaultHomePageId) ? Pages::DEFAULT_HOMEPAGE_ID : $defaultHomePageId,
            $extraAttributes,
            $this->GetAttributeValues());

        $userId = $user->Id();
        $groupId = $this->page->GetUserGroup();

        if (!empty($groupId)) {
            $group = $this->groupRepository->LoadById($groupId);
            $group->AddUser($userId);
            $this->groupRepository->Update($group);
        }

        if ($this->page->SendEmailNotification()) {
            ServiceLocator::GetEmailService()->Send(new AccountCreationForUserEmail($user, $this->page->GetPassword(), ServiceLocator::GetServer()->GetUserSession()));
        }
    }

    public function UpdateUser()
    {
        Log::Debug('Updating user %s', $this->page->GetUserId());

        $extraAttributes = array(
            UserAttribute::Organization => $this->page->GetOrganization(),
            UserAttribute::Phone => $this->page->GetPhone(),
            UserAttribute::Position => $this->page->GetPosition());

        $this->manageUsersService->UpdateUser($this->page->GetUserId(),
            $this->page->GetUserName(),
            $this->page->GetEmail(),
            $this->page->GetFirstName(),
            $this->page->GetLastName(),
            $this->page->GetTimezone(),
            $this->page->GetValidityStart(),
            $this->page->GetValidityEnd(),
            $extraAttributes);
    }

    public function DeleteUser()
    {
        $userId = $this->page->GetUserId();
        Log::Debug('Deleting user %s', $userId);

        $this->manageUsersService->DeleteUser($userId);
    }

    public function ChangePermissions()
    {
        $currentUser = $this->userRepository->LoadById(ServiceLocator::GetServer()->GetUserSession()->UserId);
        $resources = $this->GetResourcesThatCurrentUserCanAdminister($currentUser);

        $acceptableResourceIds = array();

        foreach ($resources as $resource) {
            $acceptableResourceIds[] = $resource->GetId();
        }

        $user = $this->userRepository->LoadById($this->page->GetUserId());
        $allowedResources = array();

        if (is_array($this->page->GetAllowedResourceIds())) {
            $allowedResources = $this->page->GetAllowedResourceIds();
        }

        $allowed = [];
        $view = [];
        foreach ($allowedResources as $resource)
        {
            $split = explode('_', $resource);
            $resourceId = $split[0];
            $permissionType = $split[1];

            if ($permissionType === ResourcePermissionType::Full . '')
            {
                $allowed[] = $resourceId;
            }
            else if ($permissionType === ResourcePermissionType::View . '')
            {
                $view[] = $resourceId;
            }
        }

        $currentResources = $user->GetAllowedResourceIds();
        $toRemainUnchanged = array_diff($currentResources, $acceptableResourceIds);

        $user->ChangeAllowedPermissions(array_merge($toRemainUnchanged, $allowed));
        $user->ChangeViewPermissions(array_merge($toRemainUnchanged, $view));
        $this->userRepository->Update($user);
    }

    public function ResetPassword()
    {
        $salt = $this->passwordEncryption->Salt();
        $encryptedPassword = $this->passwordEncryption->Encrypt($this->page->GetPassword(), $salt);

        $user = $this->userRepository->LoadById($this->page->GetUserId());
        $user->ChangePassword($encryptedPassword, $salt);
        $this->userRepository->Update($user);
    }

    public function ChangeAttribute()
    {
        $this->manageUsersService->ChangeAttribute($this->page->GetUserId(), $this->GetInlineAttributeValue());
    }

	public function ExportUsers()
	{
        //santosm START
        $userTimezone = $this->server->GetUserSession()->Timezone;
		$this->PageLoad($userTimezone);
        //santosm END
		$this->page->ShowExportCsv();
	}

    public function ProcessDataRequest($dataRequest)
    {
        if ($dataRequest == 'permissions') {
            $this->page->SetJsonResponse($this->GetUserResourcePermissions());
        }
        elseif ($dataRequest == 'groups') {
            $this->page->SetJsonResponse($this->GetUserGroups());
        }
        elseif ($dataRequest == 'all') {
            $users = $this->userRepository->GetAll();
            $this->page->SetJsonResponse($users);
        }
        elseif ($dataRequest == 'template') {
            $this->ShowTemplateCSV();
        }
		elseif ($dataRequest == 'export') {
			$this->ExportUsers();
		}
    }

    /**
     * @return int[] all resource ids the user has permission to
     */
    public function GetUserResourcePermissions()
    {
        $user = $this->userRepository->LoadById($this->page->GetUserId());
        return array('full' => $user->GetAllowedResourceIds(), 'view' => $user->GetAllowedViewResourceIds());

    }

    /**
     * @return array|AttributeValue[]
     */
    private function GetAttributeValues()
    {
        $attributes = array();
        foreach ($this->page->GetAttributes() as $attribute) {
            $attributes[] = new AttributeValue($attribute->Id, $attribute->Value);
        }
        return $attributes;
    }

    private function GetInlineAttributeValue()
    {
        $value = $this->page->GetValue();
        if (is_array($value)) {
            $value = $value[0];
        }
        $id = str_replace(FormKeys::ATTRIBUTE_PREFIX, '', $this->page->GetName());

        return new AttributeValue($id, $value);
    }

    protected function LoadValidators($action)
    {
        Log::Debug('Loading validators for %s', $action);

        if ($action == ManageUsersActions::UpdateUser) {
            $this->page->RegisterValidator('emailformat', new EmailValidator($this->page->GetEmail()));
            $this->page->RegisterValidator('uniqueemail',
                new UniqueEmailValidator($this->userRepository, $this->page->GetEmail(), $this->page->GetUserId()));
            $this->page->RegisterValidator('uniqueusername',
                new UniqueUserNameValidator($this->userRepository, $this->page->GetUserName(), $this->page->GetUserId()));
        }

        if ($action == ManageUsersActions::AddUser) {
            $this->page->RegisterValidator('addUserEmailformat', new EmailValidator($this->page->GetEmail()));
            $this->page->RegisterValidator('addUserUniqueemail',
                new UniqueEmailValidator($this->userRepository, $this->page->GetEmail()));
            $this->page->RegisterValidator('addUserUsername',
                new UniqueUserNameValidator($this->userRepository, $this->page->GetUserName()));
            $this->page->RegisterValidator('addAttributeValidator',
                new AttributeValidator($this->attributeService, CustomAttributeCategory::USER,
                    $this->GetAttributeValues(), null, true, true));
        }

        if ($action == ManageUsersActions::ChangeAttribute) {
            $this->page->RegisterValidator('attributeValidator',
                new AttributeValidatorInline($this->attributeService, CustomAttributeCategory::USER,
                    $this->GetInlineAttributeValue(), $this->page->GetUserId(),
                    true, true));
        }

        if ($action == ManageUsersActions::ImportUsers) {
            $this->page->RegisterValidator('fileExtensionValidator', new FileExtensionValidator('csv', $this->page->GetImportFile()));
        }

        //santosm START
        if ($action == ManageUsersActions::ImportProductors) {
            $validator = new RequiredValidator($this->page->GetNewValidityStart());
            
            $this->page->RegisterValidator('productorListRequiredValidator', new RequiredValidator($this->page->GetProductorsList()));
            $this->page->RegisterValidator('newValidityStartRequiredValidator', new RequiredValidator($this->page->GetNewValidityStart()));
            $this->page->RegisterValidator('newValidityEndRequiredValidator', new RequiredValidator($this->page->GetNewValidityEnd()));
            Log::Debug('ImportProductors!');
        }


        if ($action == ManageUsersActions::DoSanction) {
            echo('DoSanction - no validators');
        }
        //santosm END
    }

    /***
     * @return array|int[]
     */
    public function GetUserGroups()
    {
        $userId = $this->page->GetUserId();

        $user = $this->userRepository->LoadById($userId);

        $groups = array();
        foreach ($user->Groups() as $group) {
            $groups[] = $group->GroupId;
        }

        return $groups;
    }

    public function ChangeColor()
    {
        $userId = $this->page->GetUserId();
        Log::Debug('Changing reservation color for userId: %s', $userId);

        $color = $this->page->GetReservationColor();

        $user = $this->userRepository->LoadById($userId);
        $user->ChangePreference(UserPreferences::RESERVATION_COLOR, $color);

        $this->userRepository->Update($user);
    }

    public function ChangeCredits()
    {
        $userId = $this->page->GetUserId();
        $creditCount = $this->page->GetValue();

        Log::Debug('Changing credit count for userId: %s to %s', $userId, $creditCount);

        $user = $this->userRepository->LoadById($userId);
        $user->ChangeCurrentCredits($creditCount, Resources::GetInstance()->GetString('CreditsUpdatedLog', array(ServiceLocator::GetServer()->GetUserSession())));
        $this->userRepository->Update($user);
    }

    /**
     * @param User $user
     * @return BookableResource[]
     */
    private function GetResourcesThatCurrentUserCanAdminister($user)
    {
        $resources = array();
        $allResources = $this->resourceRepository->GetResourceList();
        foreach ($allResources as $resource) {
            if ($user->IsResourceAdminFor($resource)) {
                $resources[] = $resource;
            }
        }
        return $resources;
    }

    //SANTOSM START
    public function ImportProductors() { 
        $productorsList = $this->page->GetProductorsList();
        Log::Debug('function import productors! %s', $productorsList);
        preg_match_all('/(?<niu>(?<!\d)\d{7})(?>[^\d]|$)+/m', $productorsList, $matches);
        $shouldUpdate = $this->page->GetUpdateProductorOnImport();
        $validityStart = $this->page->GetNewValidityStart();
        $validityEnd = $this->page->GetNewValidityEnd();

        /**
         * @var string[] 
         */    
        $nius = $matches['niu'];
        $messages = array();
        $importCount = 0;
        $importedNIUs =array();
        foreach ($nius as $niu) {
            try {
                Log::Debug("NIU FOUND '%s'", $niu);
                if (!$shouldUpdate) {
                    $uniqueUsernameValidator = new UniqueUserNameValidator($this->userRepository, $niu);
                    $uniqueUsernameValidator->Validate();
                    if (!$uniqueUsernameValidator->IsValid()) {
                        $uuvMsgs = $uniqueUsernameValidator->Messages();
                        $messages[] = $uuvMsgs[0] . " ({$niu})";
                        continue;
                    }
                }
                $this->manageUsersService->SaveOrUpdateProductor($niu, $validityStart, $validityEnd);
                $importedNIUs[] = $niu;
                Log::Debug("NIU IMPORTED '%s'", $niu);
                $importCount++;
            } catch (Exception $ex) {
                Log::Error('Error importing users. %s', $ex);
            }

        }
        Log::Debug("NIUS IMPORTED '%s'", implode(", ", $importedNIUs));
        Log::Debug("TOTAL NIUS IMPORTED '%d'", $importCount);
        

        $this->page->SetImportProductorResult(new ProductorImportResult($importCount, $importedNIUs, $messages));
    }//SANTOSM END

    public function ImportUsers()
    {
		ini_set('max_execution_time', 600);

        $shouldUpdate = $this->page->GetUpdateOnImport();

        $attributes = $this->attributeService->GetByCategory(CustomAttributeCategory::USER);
        /** @var CustomAttribute[] $attributesIndexed */
        $attributesIndexed = array();
        /** @var CustomAttribute $attribute */
        foreach ($attributes as $attribute)
        {
            if (!$attribute->UniquePerEntity())
            {
                $attributesIndexed[strtolower($attribute->Label())] = $attribute;
            }
        }

        $groupsList = $this->groupViewRepository->GetList();
        /** @var GroupItemView[] $groups */
        $groups = $groupsList->Results();
        $groupsIndexed = array();
        foreach ($groups as $group) {
            $groupsIndexed[$group->Name()] = $group->Id();
        }

        $importFile = $this->page->GetImportFile();
        $csv = new UserImportCsv($importFile, $attributesIndexed);

        $importCount = 0;
        $messages = array();

        $rows = $csv->GetRows();

        if (count($rows) == 0) {
            $this->page->SetImportResult(new CsvImportResult(0, array(), 'Empty file or missing header row'));
            return;
        }

        for ($i = 0; $i < count($rows); $i++) {
            $row = $rows[$i];
            try {
                $emailValidator = new EmailValidator($row->email);
                $uniqueEmailValidator = new UniqueEmailValidator($this->userRepository, $row->email);
                $uniqueUsernameValidator = new UniqueUserNameValidator($this->userRepository, $row->username);

                $emailValidator->Validate();
                if (!$emailValidator->IsValid()) {
                    $evMsgs = $emailValidator->Messages();
                    $messages[] = $evMsgs[0] . " ({$row->email})";
                    continue;
                }

                if (!$shouldUpdate) {
                    $uniqueEmailValidator->Validate();
                    $uniqueUsernameValidator->Validate();

                    if (!$uniqueEmailValidator->IsValid()) {
                        $uevMsgs = $uniqueEmailValidator->Messages();
                        $messages[] = $uevMsgs[0] . " ({$row->email})";
                        continue;
                    }
                    if (!$uniqueUsernameValidator->IsValid()) {
                        $uuvMsgs = $uniqueUsernameValidator->Messages();
                        $messages[] = $uuvMsgs[0] . " ({$row->username})";
                        continue;
                    }
                }

                $timezone = empty($row->timezone) ? Configuration::Instance()->GetKey(ConfigKeys::DEFAULT_TIMEZONE) : $row->timezone;
                $password = empty($row->password) ? 'password' : $row->password;
                $language = empty($row->language) ? 'en_us' : $row->language;

                if ($shouldUpdate)
                {
                    $user = $this->manageUsersService->LoadUser($row->email);
                    if ($user->Id() == null)
                    {
                        $shouldUpdate = false;
                    }
                    else{
                        $user->ChangeName($row->firstName, $row->lastName);
                        $password = $this->passwordEncryption->EncryptPassword($row->password);
                        $user->ChangePassword($password->EncryptedPassword(), $password->Salt());
                        $user->ChangeTimezone($timezone);
                        $user->ChangeAttributes($row->phone, $row->organization, $row->position);
                    }

                }
                if (!$shouldUpdate) {
                    $user = $this->manageUsersService->AddUser($row->username, $row->email, $row->firstName, $row->lastName, $password, $timezone, $language,
                        Configuration::Instance()->GetKey(ConfigKeys::DEFAULT_HOMEPAGE),
                        array(UserAttribute::Phone => $row->phone, UserAttribute::Organization => $row->organization, UserAttribute::Position => $row->position),
                        array());
                }

                $userGroups = array();
                foreach ($row->groups as $groupName) {
                    if (array_key_exists($groupName, $groupsIndexed)) {
                        Log::Debug('Importing user %s with group %s', $row->username, $groupName);
                        $userGroups[] = new UserGroup($groupsIndexed[$groupName], $groupName);
                    }
                }

                if (count($userGroups) > 0) {
                    $user->ChangeGroups($userGroups);
                }

                foreach ($row->attributes as $label => $value)
                {
                    if (empty($value))
                    {
                        continue;
                    }
                    if (array_key_exists($label, $attributesIndexed))
                    {
                        $attribute = $attributesIndexed[$label];
                        $user->ChangeCustomAttribute(new AttributeValue($attribute->Id(), $value));
                    }
                }

                if (count($userGroups) > 0 || count($row->attributes) > 0 || $shouldUpdate)
                {
                    $this->userRepository->Update($user);
                }

                $importCount++;
            } catch (Exception $ex) {
                Log::Error('Error importing users. %s', $ex);
            }
        }

        $this->page->SetImportResult(new CsvImportResult($importCount, $csv->GetSkippedRowNumbers(), $messages));
    }

    public function InviteUsers()
	{
		$emailList = $this->page->GetInvitedEmails();
		$emails = preg_split('/[,;\s\n]+/', $emailList);
		foreach ($emails as $email)
		{
			ServiceLocator::GetEmailService()->Send(new InviteUserEmail(trim($email), ServiceLocator::GetServer()->GetUserSession()));
		}
	}

	public function DeleteMultipleUsers()
	{
		$ids = $this->page->GetDeletedUserIds();
		Log::Debug('User multiple delete. Ids=%s', implode(',', $ids));
		foreach ($ids as $id)
		{
			$this->manageUsersService->DeleteUser($id);
		}
	}

    private function ShowTemplateCSV()
    {
        $attributes = $this->attributeService->GetByCategory(CustomAttributeCategory::USER);
        $importAttributes = array();
        foreach ($attributes as $attribute)
        {
            if (!$attribute->UniquePerEntity())
            {
                $importAttributes[] = $attribute;
            }
        }
        $this->page->ShowTemplateCSV($importAttributes);
    }
}