<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\user_management\UserManagementController;
use App\Http\Controllers\api\SensorDataInsertApi;
use App\Http\Controllers\dashboard\Analytics;
use App\Http\Controllers\pages\AccountSettingsAccount;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


$controller_path = 'App\Http\Controllers';

Auth::routes();

Route::get('/login', [LoginBasic::class, 'index'])->name('login');

// authentication
Route::post('/login', [LoginBasic::class, 'login'])->name("login");
Route::get('/auth/login-basic', $controller_path . '\authentications\LoginBasic@index')->name('auth-login-basic');
Route::get('/auth/register-basic', $controller_path . '\authentications\RegisterBasic@index')->name('auth-register-basic');
Route::post('register', $controller_path . '\authentications\RegisterBasic@register')->name('register');
Route::get('/auth/forgot-password-basic', $controller_path . '\authentications\ForgotPasswordBasic@index')->name('auth-reset-password-basic');
Route::get('/logout', [LoginBasic::class, 'logout'])->name("logout");

Route::post('password/email', $controller_path . '\authentications\ForgotPasswordBasic@sendResetLinkEmail')->name('password.email');

Route::get('/getsensordata', [SensorDataInsertApi::class, 'getsensordata'])->name("getsensordata");

Route::group(['middleware' => ['auth']], function () {

	$controller_path = 'App\Http\Controllers';
	// Main Page Route
	Route::get('/', $controller_path . '\dashboard\Analytics@index')->name('dashboard-analytics');

	// User Management Start

	Route::get('/usermanagement/user-list', $controller_path . '\user_management\UserManagementController@user_list')->name('user-list');
	Route::get('/usermanagement/add-edit-user', $controller_path . '\user_management\UserManagementController@add_edit_user')->name('add-edit-user');
	Route::post('/usermanagement/insert-update-user', $controller_path . '\user_management\UserManagementController@insert_update_user')->name('insert-update-user');
	Route::get('/usermanagement/{id}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
	Route::post('/usermanagement/update-user', [UserManagementController::class, 'update_user_via_admin'])->name('update-user');
	Route::post('/usermanagement/delete-user-data', [UserManagementController::class, 'delete_user_data'])->name('delete-user-data');
	Route::get('/usermanagement/{id}/dashboard', [UserManagementController::class, 'dashboard'])->name('users.dashboard');
	// User Management End

	// Account Setting Start
	Route::post('/update-userdetails', [AccountSettingsAccount::class, 'updateUserProfile'])->name("update-userdetails");
	Route::post('/update-user-password', [AccountSettingsAccount::class, 'updateUserPassword'])->name("update-user-password");
	// Account Setting End

	// Graph Data Start
	Route::post('/getgraphdata', [Analytics::class, 'getgraphdata'])->name("getgraphdata");
	Route::post('/change-device-name', [Analytics::class, 'change_device_name'])->name("change-device-name");
	Route::post('/get-show-summary', [Analytics::class, 'get_show_summary'])->name("get-show-summary");
	// Graph Data End

	// layout
	Route::get('/layouts/without-menu', $controller_path . '\layouts\WithoutMenu@index')->name('layouts-without-menu');
	Route::get('/layouts/without-navbar', $controller_path . '\layouts\WithoutNavbar@index')->name('layouts-without-navbar');
	Route::get('/layouts/fluid', $controller_path . '\layouts\Fluid@index')->name('layouts-fluid');
	Route::get('/layouts/container', $controller_path . '\layouts\Container@index')->name('layouts-container');
	Route::get('/layouts/blank', $controller_path . '\layouts\Blank@index')->name('layouts-blank');

	// pages
	Route::get('/pages/account-settings-account', $controller_path . '\pages\AccountSettingsAccount@index')->name('pages-account-settings-account');
	Route::get('/pages/account-settings-notifications', $controller_path . '\pages\AccountSettingsNotifications@index')->name('pages-account-settings-notifications');
	Route::get('/pages/account-settings-connections', $controller_path . '\pages\AccountSettingsConnections@index')->name('pages-account-settings-connections');
	Route::get('/pages/misc-error', $controller_path . '\pages\MiscError@index')->name('pages-misc-error');
	Route::get('/pages/misc-under-maintenance', $controller_path . '\pages\MiscUnderMaintenance@index')->name('pages-misc-under-maintenance');



	// cards
	Route::get('/cards/basic', $controller_path . '\cards\CardBasic@index')->name('cards-basic');

	// User Interface
	Route::get('/ui/accordion', $controller_path . '\user_interface\Accordion@index')->name('ui-accordion');
	Route::get('/ui/alerts', $controller_path . '\user_interface\Alerts@index')->name('ui-alerts');
	Route::get('/ui/badges', $controller_path . '\user_interface\Badges@index')->name('ui-badges');
	Route::get('/ui/buttons', $controller_path . '\user_interface\Buttons@index')->name('ui-buttons');
	Route::get('/ui/carousel', $controller_path . '\user_interface\Carousel@index')->name('ui-carousel');
	Route::get('/ui/collapse', $controller_path . '\user_interface\Collapse@index')->name('ui-collapse');
	Route::get('/ui/dropdowns', $controller_path . '\user_interface\Dropdowns@index')->name('ui-dropdowns');
	Route::get('/ui/footer', $controller_path . '\user_interface\Footer@index')->name('ui-footer');
	Route::get('/ui/list-groups', $controller_path . '\user_interface\ListGroups@index')->name('ui-list-groups');
	Route::get('/ui/modals', $controller_path . '\user_interface\Modals@index')->name('ui-modals');
	Route::get('/ui/navbar', $controller_path . '\user_interface\Navbar@index')->name('ui-navbar');
	Route::get('/ui/offcanvas', $controller_path . '\user_interface\Offcanvas@index')->name('ui-offcanvas');
	Route::get('/ui/pagination-breadcrumbs', $controller_path . '\user_interface\PaginationBreadcrumbs@index')->name('ui-pagination-breadcrumbs');
	Route::get('/ui/progress', $controller_path . '\user_interface\Progress@index')->name('ui-progress');
	Route::get('/ui/spinners', $controller_path . '\user_interface\Spinners@index')->name('ui-spinners');
	Route::get('/ui/tabs-pills', $controller_path . '\user_interface\TabsPills@index')->name('ui-tabs-pills');
	Route::get('/ui/toasts', $controller_path . '\user_interface\Toasts@index')->name('ui-toasts');
	Route::get('/ui/tooltips-popovers', $controller_path . '\user_interface\TooltipsPopovers@index')->name('ui-tooltips-popovers');
	Route::get('/ui/typography', $controller_path . '\user_interface\Typography@index')->name('ui-typography');

	// extended ui
	Route::get('/extended/ui-perfect-scrollbar', $controller_path . '\extended_ui\PerfectScrollbar@index')->name('extended-ui-perfect-scrollbar');
	Route::get('/extended/ui-text-divider', $controller_path . '\extended_ui\TextDivider@index')->name('extended-ui-text-divider');

	// icons
	Route::get('/icons/boxicons', $controller_path . '\icons\Boxicons@index')->name('icons-boxicons');

	// form elements
	Route::get('/forms/basic-inputs', $controller_path . '\form_elements\BasicInput@index')->name('forms-basic-inputs');
	Route::get('/forms/input-groups', $controller_path . '\form_elements\InputGroups@index')->name('forms-input-groups');

	// form layouts
	Route::get('/form/layouts-vertical', $controller_path . '\form_layouts\VerticalForm@index')->name('form-layouts-vertical');
	Route::get('/form/layouts-horizontal', $controller_path . '\form_layouts\HorizontalForm@index')->name('form-layouts-horizontal');

	// tables
	Route::get('/tables/basic', $controller_path . '\tables\Basic@index')->name('tables-basic');


});


