<?php // Code within app\Helpers\Helper.php
namespace App\Helpers;

use Config;
use Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Models\LogActivity as LogActivityModel;

use App\Jobs\LogJob as LJ;//job log

class Helper
{

  //log
  public static function AddLog($subject, $data, $specification)
  {
    $log = [];
    $log['subject'] = $subject;
    $log['url'] = Request::fullUrl();
    $log['method'] = Request::method();
    $log['ip'] = Request::ip();
    $log['agent'] = Request::header('user-agent');
    $log['user_id'] = auth()->check() ? auth()->user()->id : 1;
    $log['data'] = $data;
    $log['created_at'] = date('Y-m-d H:i:s');
    $log['updated_at'] = date('Y-m-d H:i:s');

    if ($specification) {//false, log tidak tampil di cmd
      switch ($specification) {
        case 'error':
          Log::error($data);
          break;
        case 'alert':
          Log::alert($data);
          break;
        case 'warning':
          Log::warning($data);
          break;
        case 'info':
          Log::info($data);
          break;
        case 'debug':
          Log::debug($data);
          break;
        
        default:
          Log::emergency($data);
          break;
      }
    }
    dispatch(new LJ($log));
  }


  public static function logActivityLists()
  {
    return LogActivityModel::latest()->get();
  }

  public static function applClasses()
  {
    // Demo
    $fullURL = request()->fullurl();
    if (App()->environment() === 'production') {
      for ($i = 1; $i < 7; $i++) {
        $contains = Str::contains($fullURL, 'demo-' . $i);
        if ($contains === true) {
          $data = config('custom.' . 'demo-' . $i);
        }
      }
    } else {
      $data = config('custom.custom');
    }


    // default data value
    $dataDefault = [
      'mainLayoutType' => 'vertical-menu',
      'theme' => 'light',
      'isContentSidebar' => false,
      'pageHeader' => false,
      'bodyCustomClass' => '',
      'navbarBgColor' => 'bg-white',
      'navbarType' => 'fixed',
      'isMenuCollapsed' => false,
      'footerType' => 'static',
      'templateTitle' => '',
      'isCustomizer' => true,
      'isCardShadow' => true,
      'isScrollTop' => true,
      'defaultLanguage' => 'en',
      'direction' => env('MIX_CONTENT_DIRECTION', 'ltr'),
    ];

    // if any key missing of array from custom.php file it will be merge and set a default value from dataDefault array and store in data variable
    $data = array_merge($dataDefault, $data);

    // all available option of materialize template
    $allOptions = [
      'mainLayoutType' => array('vertical-menu', 'horizontal-menu', 'vertical-menu-boxicons'),
      'theme' => array('light' => 'light', 'dark' => 'dark', 'semi-dark' => 'semi-dark'),
      'isContentSidebar' => array(false, true),
      'pageHeader' => array(false, true),
      'bodyCustomClass' => '',
      'navbarBgColor' => array('bg-white', 'bg-primary', 'bg-success', 'bg-danger', 'bg-info', 'bg-warning', 'bg-dark'),
      'navbarType' => array('fixed' => 'fixed', 'static' => 'static', 'hidden' => 'hidden'),
      'isMenuCollapsed' => array(false, true),
      'footerType' => array('fixed' => 'fixed', 'static' => 'static', 'hidden' => 'hidden'),
      'templateTitle' => '',
      'isCustomizer' => array(true, false),
      'isCardShadow' => array(true, false),
      'isScrollTop' => array(true, false),
      'defaultLanguage' => array('en' => 'en', 'pt' => 'pt', 'fr' => 'fr', 'de' => 'de'),
      'direction' => array('ltr' => 'ltr', 'rtl' => 'rtl'),
    ];
    // navbar body class array
    $navbarBodyClass = [
      'fixed' => 'navbar-sticky',
      'static' => 'navbar-static',
      'hidden' => 'navbar-hidden',
    ];
    $navbarClass  = [
      'fixed' => 'fixed-top',
      'static' => 'navbar-static-top',
      'hidden' => 'd-none',
    ];
    // footer class
    $footerBodyClass = [
      'fixed' => 'fixed-footer',
      'static' => 'footer-static',
      'hidden' => 'footer-hidden',
    ];
    $footerClass = [
      'fixed' => 'footer-sticky',
      'static' => 'footer-static',
      'hidden' => 'd-none',
    ];

    //if any options value empty or wrong in custom.php config file then set a default value
    foreach ($allOptions as $key => $value) {
      if (gettype($data[$key]) === gettype($dataDefault[$key])) {
        if (is_string($data[$key])) {
          if (is_array($value)) {

            $result = array_search($data[$key], $value);
            if (empty($result)) {
              $data[$key] = $dataDefault[$key];
            }
          }
        }
      } else {
        if (is_string($dataDefault[$key])) {
          $data[$key] = $dataDefault[$key];
        } elseif (is_bool($dataDefault[$key])) {
          $data[$key] = $dataDefault[$key];
        } elseif (is_null($dataDefault[$key])) {
          is_string($data[$key]) ? $data[$key] = $dataDefault[$key] : '';
        }
      }
    }

    //  above arrary override through dynamic data
    $layoutClasses = [
      'mainLayoutType' => $data['mainLayoutType'],
      'theme' => $data['theme'],
      'isContentSidebar' => $data['isContentSidebar'],
      'pageHeader' => $data['pageHeader'],
      'bodyCustomClass' => $data['bodyCustomClass'],
      'navbarBgColor' => $data['navbarBgColor'],
      'navbarType' => $navbarBodyClass[$data['navbarType']],
      'navbarClass' => $navbarClass[$data['navbarType']],
      'isMenuCollapsed' => $data['isMenuCollapsed'],
      'footerType' => $footerBodyClass[$data['footerType']],
      'footerClass' => $footerClass[$data['footerType']],
      'templateTitle' => $data['templateTitle'],
      'isCustomizer' => $data['isCustomizer'],
      'isCardShadow' => $data['isCardShadow'],
      'isScrollTop' => $data['isScrollTop'],
      'defaultLanguage' => $data['defaultLanguage'],
      'direction' => $data['direction'],
    ];

    // set default language if session hasn't locale value the set default language
    if (!session()->has('locale')) {
      app()->setLocale($layoutClasses['defaultLanguage']);
    }

    return $layoutClasses;
  }
  // updatesPageConfig function override all configuration of custom.php file as page requirements.
  public static function updatePageConfig($pageConfigs)
  {
    $demo = 'custom';
    $custom = 'custom';
    $fullURL = request()->fullurl();
    if (App()->environment() === 'production') {
      for ($i = 1; $i < 7; $i++) {
        $contains = Str::contains($fullURL, 'demo-' . $i);
        if ($contains === true) {
          $demo = 'demo-' . $i;
        }
      }
    }
    if (isset($pageConfigs)) {
      if (count($pageConfigs) > 0) {
        foreach ($pageConfigs as $config => $val) {
          Config::set('custom.' . $demo . '.' . $config, $val);
        }
      }
    }
  }
}
