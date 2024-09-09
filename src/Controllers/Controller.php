<?php
namespace ScriptingThoughts\Controllers;

abstract class Controller {
    protected $brand = "Scripting Thoughts";
    protected $pageTitle;
    protected $message;
    protected $session;
    protected $userControl;
    protected $mail;
    protected $http;
    protected $uploadcare;
    protected $account;

    protected $errors = [];
    protected $errorList = [];

    protected function renderView(string $filePath, array $variables = []): string
    {
        ob_start();
        extract($variables, EXTR_OVERWRITE);
        include($filePath);

        return ob_get_clean();
    }

    /**
     * Get filtered $_POST values.
     * Return an array.
     */
    protected function filter_post()
    {
        $post = filter_input_array(INPUT_POST);
        $post = array_map('trim', $post);
        $post = array_map('htmlspecialchars', $post);

        return $post;
    }

    /**
     * Get filtered $_GET values.
     * Return an array.
     */
    protected function filter_get()
    {
        var_dump($_SERVER['REQUEST_URI']);
        var_dump($_GET);
        $get = filter_input_array(INPUT_GET);
        
        $get = array_map('trim', $get);
        $get = array_map('htmlspecialchars', $get);

        return $get;
    }

    /**
     * Get URI path.
     * Return a string.
     */
    protected function getUri()
    {

        $uri = htmlspecialchars($_SERVER['REQUEST_URI']);
        $uri = urldecode(trim(parse_url($uri, PHP_URL_PATH), '/'));

        return $uri;
    }

    /** Get request method.
     * Return a string.
     */
    protected function getMethod()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        return $method;
    }

    protected function isValidObjectId($id) {
        return preg_match('/^[0-9a-fA-F]{24}$/', $id) === 1;
    }


    protected function escape($html)
    {
        return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Shortcut to retrieve JavaScript file from the /js/ directory.
     * Returns a URL.
     */
    protected function getScript($filename)
    {
        $file = strtolower($filename);

        return PROTOCOL . $_SERVER['HTTP_HOST'] . '/js/' . $file . '.js';
    }

    /**
     * Shortcut to retrieve stylesheet file from the /css/ directory.
     * Returns a URL.
     */
    protected function getStylesheet($filename)
    {
        $file = strtolower($filename);
        
        return PROTOCOL . $_SERVER['HTTP_HOST'] . '/css/' . $file . '.css';
    }

    /**
     * Shortcut to retrieve image file from the /images/ directory.
     * Returns a URL.
     */
    protected function getImage($filename)
    {
        $file = strtolower($filename);

        return PROTOCOL . $_SERVER['HTTP_HOST'] . '/assets/images/' . $file;
    }

    protected function getIcon($filename)
    {
        $file = strtolower($filename);

        return PROTOCOL . $_SERVER["HTTP_HOST"] . "/assets/icons/" . $file;
    }
    /**
     * Retrieve a view URL by filename.
     * Requires a file.
     */
    protected function view(string $view, array $variables = []) 
    {
        $view = strtolower($view);

        ob_start();
        extract($variables, EXTR_OVERWRITE);
        include($_SERVER['DOCUMENT_ROOT'] . '/../src/views/' . $view . '.view.php');

        echo ob_get_clean();

    }


    protected function component($component) 
    {
        $component = strtolower($component);

        return $_SERVER['DOCUMENT_ROOT'] . '/../src/views/components/' . $component . '.component.php'; 
    }

    protected function partial($partial) :string
    {
        $partial = strtolower($partial);

        ob_start();
        include($_SERVER['DOCUMENT_ROOT'] . '/../src/views/partials/' . $partial . '.php');

        return ob_get_clean();
    }

    protected function emailBodyComponent($component) 
    {
        $component = strtolower($component);

        return $_SERVER['DOCUMENT_ROOT'] . '/../src/views/components/email/' . $component . '.component.php'; 
    }

    /**
     * Check if the current page is the Index.
     * Returns a Boolean.
     */
    protected function isIndex()
    {
        $redirect = ltrim($_SERVER['REDIRECT_URL'], '/');

        return $redirect === '';
    }

    /**
     * Check if the current page is specified page.
     * Returns a Boolean.
     */
    protected function isPage($view)
    {
        $view = strtolower($view);

        $redirect = ltrim($_SERVER['REDIRECT_URL'], '/');

        return $redirect === $view;
    }

    /**
     * Redirects to the specified page.
     */
    protected function redirect($view)
    {
        $view = strtolower($view);

        header('Location: /' . $view);
        exit;
    }

    /**
     * Securely hash a password.
     * Returns hashed password.
     */
    protected function encryptPassword($password)
    {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT, array('cost' => 12));

        return $passwordHash;
    }

    /**
     * Vertify a submitted password against existing password.
     * Return a Boolean.
     */
    protected function verifyPassword($submittedPassword, $password)
    {
        if(!password_verify($submittedPassword, $password)) {
            $this->errors[] = OLD_PASSWORD_INCORRECT;
        }
    }

    function verifyNewPassword($newPassword, $currentPasswordHash) {
        // Use password_verify to compare the new password with the stored hash
        if (password_verify($newPassword, $currentPasswordHash)) {
            $this->errors[] = PASSWORD_SAME_AS_OLD;
        } 
    }

    /**
     * Check if a username is in the list of disallowed usernames.
     * Return a Boolean.
     */
    protected function isApprovedUsername($username)
    {
        $approved = in_array($username, DISALLOWED_USERNAMES) ? false : true;

        return $approved;
    }

    /**
     * Check if username is empty, and make sure it only contains
     * alphanumeric characters, numbers, dashes, and underscores.
     * Return an error or null.
     */
    protected function validateUsername($username)
    {
        if (!empty($username)) {
            if (strlen($username) < '3') {
                $this->errors[] = USERNAME_TOO_SHORT;
            }
            if (strlen($username) > '20') {
                $this->errors[] = USERNAME_TOO_LONG;
            }
            // Match a-z, A-Z, 1-9, -, _.
            if (!preg_match("/^[a-zA-Z\d\-_]+$/i", $username)) {
                $this->errors[] = USERNAME_CONTAINS_DISALLOWED;
            }
            if($this->userControl->isUsernameAvailable($username)) {
                $this->errors[] = USERNAME_EXISTS;
            }
        } else {
            $this->errors[] = USERNAME_MISSING;
        }
    }

    /**
     * Check if password is empty, and make sure it conforms
     * to password security standards.
     * Return an error or null.
     */
    protected function validatePassword($password)
    {
        if (!empty($password)) {
            if (strlen($password) < '8') {
                $this->errors[] = PASSWORD_TOO_SHORT;
            }
            if (!preg_match("#[0-9]+#", $password)) {
                $this->errors[] = PASSWORD_NEEDS_NUMBER;
            }
            if (!preg_match("#[A-Z]+#", $password)) {
                $this->errors[] = PASSWORD_NEEDS_UPPERCASE;
            }
            if (!preg_match("#[a-z]+#", $password)) {
                $this->errors[] = PASSWORD_NEEDS_LOWERCASE;
            }
        } else {
            $this->errors[] = PASSWORD_MISSING;
        }
    }

    /**
     * Check if email is empty, and test it against PHP built in
     * email validation.
     * Return an error or null.
     */
    protected function validateEmail($email)
    {
        if (!empty($email)) {
            // Remove all illegal characters from email
            $email = filter_var($email, FILTER_SANITIZE_EMAIL);

            // Validate e-mail
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->errors[] = EMAIL_NOT_VALID;
            } else {
                return $email;
            }
        } else {
            $this->errors[] = EMAIL_MISSING;
        }
    }

    /**
     * Get list of errors from validation.
     * Return a string.
     */
    protected function getErrors($errors)
    {
        foreach ($errors as $error) {
            $this->errorList .= $error . "\n";
        }
        return $this->errorList;
    }

    protected function timeSince($date) {
        $timestamp = strtotime($date);
        $seconds = time() - $timestamp;
    
        $minutes = floor($seconds / 60);
        $hours = floor($seconds / 3600);
        $days = floor($seconds / 86400);
        $months = floor($seconds / 2629743);
        $years = floor($seconds / 31556926);
    
        if ($years > 0) {
            return $years . " year" . ($years > 1 ? "s" : "") . " ago";
        } elseif ($months > 0) {
            return $months . " month" . ($months > 1 ? "s" : "") . " ago";
        } elseif ($days > 0) {
            return $days . " day" . ($days > 1 ? "s" : "") . " ago";
        } elseif ($hours > 0) {
            return $hours . " hour" . ($hours > 1 ? "s" : "") . " ago";
        } elseif ($minutes > 0) {
            return $minutes . " minute" . ($minutes > 1 ? "s" : "") . " ago";
        } else {
            return $seconds . " second" . ($seconds > 1 ? "s" : "") . " ago";
        }
    }
    
}