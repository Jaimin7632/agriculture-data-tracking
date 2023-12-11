<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
  /**
   * Register services.
   *
   * @return void
   */
  public function register()
  {
    //
  }

  /**
   * Bootstrap services.
   *
   * @return void
   */
  public function boot()
  {
    $verticalMenuJson = file_get_contents(base_path('resources/menu/verticalMenu.json'));
    $verticalMenuUserJson = file_get_contents(base_path('resources/menu/verticalMenuUser.json'));
    $verticalMenuData = json_decode($verticalMenuJson);
    $verticalMenuUserData = json_decode($verticalMenuUserJson);

    // Share all menuData to all the views
    \View::share('menuData', [$verticalMenuData]);
    \View::share('menuuserData', [$verticalMenuUserData]);
  }
}
