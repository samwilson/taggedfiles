<?php

namespace App\Controllers;

use App\Config;
use App\User;
use App\Template;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swift_Message;
use Zend\Diactoros\Response\RedirectResponse;

class UserController extends Base
{

    public function loginForm(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $template = new Template('login.twig');
        $template->title = 'Log in';
        $template->name = $this->getQueryParam($request, 'name');
        $response->getBody()->write($template->render());
        return $response;
    }

    public function login(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $name = $this->getBodyParam($request, 'name');
        $nameQueryString = $name ? "?name=$name" : '';
        if ($this->getBodyParam($request, 'register')) {
            return new RedirectResponse($this->config->baseUrl() . '/register' . $nameQueryString);
        }
        if ($this->getBodyParam($request, 'remind')) {
            return new RedirectResponse($this->config->baseUrl() . '/remind' . $nameQueryString);
        }
        $template = new Template('login.twig');
        $user = $this->db->query('SELECT id, password FROM users WHERE name=:n', ['n' => $name])->fetch();
        if (isset($user->password)) {
            if (password_verify($this->getBodyParam($request, 'password'), $user->password)) {
                session_regenerate_id(true);
                $_SESSION['userid'] = $user->id;
                return new RedirectResponse($this->config->baseUrl());
            }
        }
        $template->alert('warning', 'Acess denied.', true);
        return new RedirectResponse($this->config->baseUrl() . '/login?name=' . $name);
    }

    public function logout(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        session_unset();
        session_destroy();
        session_write_close();
        setcookie(session_name(), '', 0, '/');
        session_regenerate_id(true);
        return new RedirectResponse($this->config->baseUrl() . '/login');
    }

    public function registerForm(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $template = new Template('register.twig');
        $template->title = 'Register';
        $template->name = $this->getAnyParam($request, 'name');
        $template->email = $this->getBodyParam($request, 'email');
        $response->getBody()->write($template->render());
        return $response;
    }

    public function register(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $name = $this->getBodyParam($request, 'name');
        $email = $this->getBodyParam($request, 'email');
        $template = new Template('register.twig');
        if ($this->getBodyParam($request, 'login')) {
            return new RedirectResponse($this->config->baseUrl() . '/login?name=' . $name);
        }
        $password = $this->getBodyParam($request, 'password');
        if ($password !== $this->getBodyParam($request, 'password-confirmation')) {
            $template->alert('warning', 'Your passwords did not match.', true);
            return new RedirectResponse($this->config->baseUrl() . "/register?name=$name&email=$email");
        }
        $user = new User($this->db);
        $user->register($name, $email, $password);
        $template->alert('success', 'Thank you for registering.', true);
        return new RedirectResponse($this->config->baseUrl() . '/login?name=' . $user->getName());
    }

    public function remindForm(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $template = new Template('remind.twig');
        $template->title = 'Remind';
        $template->name = $this->getBodyParam($request, 'name');
        $response->getBody()->write($template->render());
        return $response;
    }

    public function remind(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $name = $this->getBodyParam($request, 'name');
        if ($this->getBodyParam($request, 'login')) {
            return new RedirectResponse($this->config->baseUrl() . '/login?name=' . $name);
        }
        $config = new Config();
        $user = new User($this->db);
        $user->loadByName($name);
        $template = new Template('remind_email.twig');
        if (!empty($user->getEmail())) {
            $template->user = $user;
            $template->token = $user->getReminder();
            $message = Swift_Message::newInstance()
                ->setSubject('Password reminder')
                ->setFrom(array($config->siteEmail() => $config->siteTitle()))
                ->setTo(array($user->getEmail() => $user->getName()))
                ->setBody($template->render(), 'text/html');
            $this->email($message);
        } else {
            // Pause for a moment, so it's not so obvious which users' names are resulting in mail being sent.
            sleep(5);
        }
        $template->alert('success', 'Please check your email', true);
        return new RedirectResponse($this->config->baseUrl() . '/remind?name=' . $name);
    }

    public function remindResetForm(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        if (!isset($args['token'])) {
            return new RedirectResponse($this->config->baseUrl() . '/remind');
        }
        $template = new Template('remind_reset.twig');
        $template->title = 'Password Reset';
        $template->userid = $args['userid'];
        $template->token = $args['token'];
        $response->getBody()->write($template->render());
        return $response;
    }

    public function remindReset(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $template = new Template('remind_reset.twig');
        // First check that the passwords match.
        $password = $request->getAttribute('password');
        if ($password !== $request->getAttribute('password-confirmation')) {
            $template->alert('warning', 'Your passwords did not match.', true);
            return new RedirectResponse($this->config->baseUrl() . "/remind/" . $args['userid'] . "/" . $args['token']);
        }
        // Then see if the token is valid.
        $user = new User($this->db);
        $user->load($args['userid']);
        if (!$user->checkReminderToken($args['token'])) {
            $template->alert('warning', 'That reminder token has expired. Please try again.', true);
            return new RedirectResponse($this->config->baseUrl() . "/remind");
        }
        // Finally change the password. This will delete the token as well.
        $user->changePassword($password);
        $template->alert('success', 'Your password has been changed. Please log in.', true);
        return new RedirectResponse($this->config->baseUrl() . "/login?name=" . $user->getName());
    }

    public function profile(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $template = new Template('profile.twig');
        $template->title = 'Profile';
        $response->getBody()->write($template->render());
        return $response;
    }
}
