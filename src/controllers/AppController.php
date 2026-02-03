<?php

class AppController {

    protected function isGet(): bool {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    protected function isPOST(): bool {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function render(string $template = null, array $variables = []) {
        $templatePath = __DIR__ . '/../../public/views/' . $template . '.html';
        $output = 'File not found';
        

        $this->initSession();
        if (!isset($variables['csrf']) && isset($_SESSION['csrf'])) {
            $variables['csrf'] = $_SESSION['csrf'];
        }

        extract($variables);

        if(file_exists($templatePath)){
            ob_start();
            include $templatePath;
            $output = ob_get_clean();
        }
        ob_start();
        include __DIR__ . '/../../public/views/main.html';
        $finalOutput = ob_get_clean();

        echo $finalOutput;
    }

    protected function renderStandalone(string $template = null, array $variables = []) {
        $templatePath = __DIR__ . '/../../public/views/' . $template . '.html';
        
        $this->initSession(); 
        if (!isset($variables['csrf']) && isset($_SESSION['csrf'])) {
            $variables['csrf'] = $_SESSION['csrf'];
        }


        extract($variables);

        if(file_exists($templatePath)){
            include $templatePath;
        } else {
            echo "Template $template not found";
        }
    }

    protected function requireLogin() { 
        $this->initSession();
    
        if (empty($_SESSION['user_id'])) { 
            $url = "http://$_SERVER[HTTP_HOST]"; 
            header("Location: {$url}/login"); 
            exit(); 
        } 
    } 

    protected function generateCsrf(): void {
        $this->initSession();

        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
    }

    protected function initSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params(['httponly' => true]);
            session_set_cookie_params(['secure' => true]);
            session_set_cookie_params(['samesite' => 'Strict']);
            session_start();
        }
    }
}