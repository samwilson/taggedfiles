<?php

namespace App\Controllers;

use App\Config;
use App\User;
use App\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class UserController extends Base {

    public function loginForm(Request $request, Response $response, array $args) {
        $template = new \App\Template('login.twig');
        $template->title = 'Log in';
        $template->name = $request->get('name');
        $response->setContent($template->render());
        return $response;
    }

    public function login(Request $request, Response $response, array $args) {
        $name = $request->get('name');
        if ($request->get('register')) {
            return new RedirectResponse($this->config->baseUrl().'/register?name='.$name);
        }
        if ($request->get('remind')) {
            return new RedirectResponse($this->config->baseUrl().'/remind?name='.$name);
        }
        $template = new \App\Template('login.twig');
        $user = $this->db->query('SELECT id, password FROM users WHERE name=:n', ['n'=>$name])->fetch();
        if (isset($user->password)) {
            if (password_verify($request->get('password'), $user->password)) {
                session_regenerate_id(true);
                $_SESSION['userid'] = $user->id;
                return new RedirectResponse($this->config->baseUrl());
            }
        }
        $template->alert('warning', 'Acess denied.', true);
        return new RedirectResponse($this->config->baseUrl().'/login?name='.$name);
    }

    public function logout(Request $request, Response $response, array $args) {
        session_unset();
        session_destroy();
        session_write_close();
        setcookie(session_name(),'',0,'/');
        session_regenerate_id(true);
        return new RedirectResponse($this->config->baseUrl().'/login');
    }

    public function registerForm(Request $request, Response $response, array $args) {
        $template = new \App\Template('register.twig');
        $template->title = 'Register';
        $template->name = $request->get('name');
        $template->email = $request->get('email');
        $response->setContent($template->render());
        return $response;
    }

    public function register(Request $request, Response $response, array $args) {
        $name = $request->get('name');
        $email = $request->get('email');
        $template = new \App\Template('register.twig');
        if ($request->get('login')) {
            return new RedirectResponse($this->config->baseUrl().'/login?name='.$name);
        }
        $password = $request->get('password');
        if ($password !== $request->get('password-confirmation')) {
            $template->alert('warning', 'Your passwords did not match.', true);
            return new RedirectResponse($this->config->baseUrl()."/register?name=$name&email=$email");
        }
        $user = new User($this->db);
        $user->register($name, $email, $password);
        $template->alert('success', 'Thank you for registering.', true);
        return new RedirectResponse($this->config->baseUrl().'/login?name='.$user->getName());
    }

    public function remindForm(Request $request, Response $response, array $args)
    {
        $template = new \App\Template('remind.twig');
        $template->title = 'Remind';
        $template->name = $request->get('name');
        $response->setContent($template->render());
        return $response;
    }

    public function remind(Request $request, Response $response, array $args) {
        $name = $request->get('name');
        if ($request->get('login')) {
            return new RedirectResponse($this->config->baseUrl().'/login?name='.$name);
        }
        $config = new Config();
        $user = new User($this->db);
        $user->loadByName($name);
        $template = new Template('remind_email.twig');
        if (!empty($user->getEmail())) {
            $template->user = $user;
            $template->token = $user->getReminder();
            $message = \Swift_Message::newInstance()
                ->setSubject('Password reminder')
                ->setFrom(array($config->siteEmail() => $config->siteTitle()))
                ->setTo(array($user->getEmail() => $user->getName()))
                ->setBody($template->render(), 'text/html');
            $this->email($message);
        }
        $template->alert('success', 'Please check your email', true);
        return new RedirectResponse($this->config->baseUrl().'/remind?name='.$user->getName());
    }

    public function remindResetForm(Request $request, Response $response, array $args) {
        if (!isset($args['token'])) {
            return new RedirectResponse($this->config->baseUrl().'/remind');
        }
        $template = new \App\Template('remind_reset.twig');
        $template->title = 'Password Reset';
        $template->userid = $args['userid'];
        $template->token = $args['token'];
        $response->setContent($template->render());
        return $response;
    }

    public function remindReset(Request $request, Response $response, array $args) {
        $template = new \App\Template('remind_reset.twig');
        // First check that the passwords match.
        $password = $request->get('password');
        if ($password !== $request->get('password-confirmation')) {
            $template->alert('warning', 'Your passwords did not match.', true);
            return new RedirectResponse($this->config->baseUrl()."/remind/".$args['userid']."/".$args['token']);
        }
        // Then see if the token is valid.
        $user = new User($this->db);
        $user->load($args['userid']);
        if (!$user->checkReminderToken($args['token'])) {
            $template->alert('warning', 'That reminder token has expired. Please try again.', true);
            return new RedirectResponse($this->config->baseUrl()."/remind");
        }
        // Finally change the password. This will delete the token as well.
        $user->changePassword($password);
        $template->alert('success', 'Your password has been changed. Please log in.', true);
        return new RedirectResponse($this->config->baseUrl()."/login?name=".$user->getName());
    }
}
