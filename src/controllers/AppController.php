<?php


class AppController {

    protected function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
    protected function isPOST(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    protected function render(string $template = null, array $variables = [])
    {
        $templatePath = __DIR__.'/../../public/views/'. $template.'.html';
        $templatePath404 = __DIR__.'/../../public/views/404.html';
        $output = "";
                 
        if(file_exists($templatePath)){
            extract($variables);
            
            ob_start();
            include $templatePath;
            $output = ob_get_clean();
        } else {
            ob_start();
            include $templatePath404;
            $output = ob_get_clean();
        }
        echo $output;
    }

    protected function requireLogin() 
    { 
        $this->initSession();
    
        if (empty($_SESSION['user_id'])) { 
            $url = "http://$_SERVER[HTTP_HOST]"; 
            header("Location: {$url}/login"); 
            exit(); 
        } 
    } 

    protected function generateCsrf(): void
    {
        $this->initSession();

        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
    }


    protected function initSession() 
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params(['httponly' => true]);
            session_set_cookie_params(['secure' => true]);
            session_set_cookie_params(['samesite' => 'Strict']);
            session_start();
        }
    }
}