<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Auth.php';

class AuthController extends Controller {

    public function showLogin(): void {
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }
        $this->render('auth/login');
    }

    public function login(): void {
        $email = trim($_POST['email'] ?? '');
        $senha = trim($_POST['senha'] ?? '');

        if (empty($email) || empty($senha)) {
            $_SESSION['error'] = 'Preencha o e-mail e a senha.';
            $this->redirect('/login');
        }

        if (Auth::login($email, $senha)) {
            $this->redirect('/dashboard');
        } else {
            $_SESSION['error'] = 'E-mail ou senha incorretos, ou conta inativa.';
            $this->redirect('/login');
        }
    }

    public function logout(): void {
        Auth::logout();
        $this->redirect('/login');
    }
}
