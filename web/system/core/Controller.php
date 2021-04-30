<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author    EllisLab Dev Team
 * @copyright    Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright    Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license    http://opensource.org/licenses/MIT	MIT License
 * @link    https://codeigniter.com
 * @since    Version 1.0.0
 * @filesource
 */

use System\Libraries\Core;

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Application Controller Class
 *
 * This class object is the super class that every library in
 * CodeIgniter will be assigned to.
 *
 * @package        CodeIgniter
 * @subpackage    Libraries
 * @category    Libraries
 * @author        EllisLab Dev Team
 * @link        https://codeigniter.com/user_guide/general/controllers.html
 */

/**
 * PhpStorm Code Completion to CodeIgniter + HMVC
 *
 * @package       CodeIgniter
 * @subpackage    PhpStorm
 * @category      Code Completion
 * @version       3.1.4
 * @author        Natan Felles
 * @link          http://github.com/natanfelles/codeigniter-phpstorm
 */

/*
 * To enable code completion to your own libraries add a line above each class as follows:
 *
 * @property Library_name       $library_name                        Library description
 *
 */

/**
 * @property CI_Benchmark $benchmark                           This class enables you to mark
 *     points and calculate the time difference between them. Memory consumption can also be displayed.
 * @property CI_Calendar $calendar                            This class enables the
 *     creation of calendars
 * @property CI_Cache $cache                               Caching Class
 * @property CI_Config $config                              This class contains functions
 *     that enable config files to be managed
 * @property CI_Controller $controller                          This class object is the super
 *     class that every library in CodeIgniter will be assigned to
 * @property CI_DB_forge $dbforge                             Database Forge Class
 * @property CI_DB_mysql_driver|CI_DB_query_builder $db                                  This is the
 *     platform-independent base Query Builder implementation class
 * @property CI_DB_utility $dbutil                              Database Utility Class
 * @property CI_Driver_Library $driver                              Driver Library Class
 * @property CI_Email $email                               Permits email to be sent using
 *     Mail, Sendmail, or SMTP
 * @property CI_Encrypt $encrypt                             Provides two-way keyed
 *     encoding using Mcrypt
 * @property CI_Encryption $encryption                          Provides two-way keyed
 *     encryption via PHP's MCrypt and/or OpenSSL extensions
 * @property CI_Exceptions $exceptions                          Exceptions Class
 * @property CI_Form_validation $form_validation                     Form Validation Class
 * @property CI_FTP $ftp                                 FTP Class
 * @property CI_Hooks $hooks                               Provides a mechanism to extend
 *     the base system without hacking
 * @property CI_Image_lib $image_lib                           Image Manipulation class
 * @property CI_Input $input                               Pre-processes global input
 *     data for security
 * @property CI_Jquery $jquery                              Jquery Class
 * @property CI_Log $log                                 Logging Class
 * @property CI_Lang $lang                           Language Manipulation class
 * @property CI_Migration $migration                           All migrations should
 *     implement this, forces up() and down() and gives access to the CI super-global
 * @property System\Core\CI_Model $model                               CodeIgniter Model Class
 * @property CI_Output $output                              Responsible for sending final
 *     output to the browser
 * @property CI_Pagination $pagination                          Pagination Class
 * @property CI_Parser $parser                              Parser Class
 * @property CI_Profiler $profiler                            This class enables you to
 *     display benchmark, query, and other data in order to help with debugging and optimization.
 * @property CI_Router $router                              Parses URIs and determines
 *     routing
 * @property CI_Security $security                            Security Class
 * @property CI_Session $session                             Session Class
 * @property CI_Table $table                               Lets you create tables
 *     manually or from database result objects, or arrays
 * @property CI_Trackback $trackback                           Trackback Sending/Receiving
 *     Class
 * @property CI_Typography $typography                          Typography Class
 * @property CI_Unit_test $unit                                Simple testing class
 * @property CI_Upload $upload                              File Uploading Class
 * @property CI_URI $uri                                 Parses URIs and determines
 *     routing
 * @property CI_User_agent $agent                               Identifies the platform,
 *     browser, robot, or mobile device of the browsing agent
 * @property CI_Xmlrpc $xmlrpc                              XML-RPC request handler class
 * @property CI_Xmlrpcs $xmlrpcs                             XML-RPC server class
 * @property CI_Zip $zip                                 Zip Compression Class
 * @property CI_Utf8 $utf8                                Provides support for UTF-8
 *     environments
 *
 *
 */
class CI_Controller {

    /**
     * Reference to the CI singleton
     *
     * @var CI_Controller
     */
    private static $instance;

    /**
     * CI_Loader
     *
     * @var CI_Loader
     */
    public $load;

    /**
     * @var Sparrow
     */
    public $s;

    /**
     * Class constructor
     *
     * @return    void
     */
    public function __construct()
    {
        self::$instance =& $this;

        // Assign all the class objects that were instantiated by the
        // bootstrap file (CodeIgniter.php) to local class variables
        // so that CI can run as one big super object.
        foreach (is_loaded() as $var => $class)
        {
            $this->$var =& load_class($class);
		}

		$this->load =& load_class('Loader', 'core');
		$this->load->initialize();
		log_message('info', 'Controller Class Initialized');
	}

	// --------------------------------------------------------------------

    /**
     * Get the CI singleton
     *
     * @static
     * @return    CI_Controller
     */
    public static function &get_instance()
    {
        return self::$instance;
    }


    /**
     * @param array $data
     * @param int $http_code
     * @return object|string|void
     */
    public function response($data = [], int $http_code = 200)
    {
        App::get_ci()->output->set_status_header($http_code);
        App::get_ci()->output->set_content_type('application/json')->set_output(json_encode($data));
        return;
    }

    /**
     * @param string $template
     * @param mixed $data
     * @return object|string|void
     */
    public function response_view(string $template, $data = [])
    {
        // For development purposes we used to add this to see in network panel requests, and navigate to page and see data
        if (App::get_ci()->input->is_ajax_request() && App::get_ci()->input->is_pjax_request() == FALSE && is_dev())
        {
            return $this->response($data);
        }

        if (defined('IS_SPA_APPLICATION') && IS_SPA_APPLICATION)
        {
            return $this->response($data);
        }

        return $this->load->view($template, $data);
    }

    /**
     * @param array $data
     * @param int $http_code
     * @return object|string|void
     */
    public function response_success(array $data = [], int $http_code = 200)
    {
        $data['status'] = Core::RESPONSE_STATUS_SUCCESS;
        App::get_ci()->output->set_status_header($http_code);
        App::get_ci()->output->set_content_type('application/json')->set_output(json_encode($data));
        return;
    }

    /**
     * @param array $data
     * @param int $http_code
     * @return object|string|void
     */
    public function response_info(array $data = [], int $http_code = 200)
    {
        $data['status'] = Core::RESPONSE_STATUS_INFO;
        App::get_ci()->output->set_status_header($http_code);
        App::get_ci()->output->set_content_type('application/json')->set_output(json_encode($data));
        return;
    }

    /**
     * @param string $error_message
     * @param array $data
     * @param int $http_code
     * @return object|string|void
     */
    public function response_error(string $error_message = 'error_core_internal', $data = [], int $http_code = 200)
    {
        $data['status'] = Core::RESPONSE_STATUS_ERROR;
        $data['error_message'] = $error_message;
        App::get_ci()->output->set_status_header($http_code);
        App::get_ci()->output->set_content_type('application/json')->set_output(json_encode($data));
        return;
    }

}
