<?php

namespace App\Controllers;

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
        $user = $this->db->query('SELECT password FROM users WHERE name=:n', ['n'=>$name])->fetch();
        if (isset($user->password)) {
            if (password_verify($request->get('password'), $user->password)) {
                return new RedirectResponse($this->config->baseUrl());
            }
        }
        return new RedirectResponse($this->config->baseUrl().'/login?name='.$name);
    }

    public function registerForm(Request $request, Response $response, array $args) {
        $template = new \App\Template('register.twig');
        $template->title = 'Register';
        $template->name = $request->get('name');
        $response->setContent($template->render());
        return $response;
    }

    public function register(Request $request, Response $response, array $args) {
        $name = $request->get('name');
        if ($request->get('login')) {
            return new RedirectResponse($this->config->baseUrl().'/login?name='.$name);
        }
        if ($request->get('password') !== $request->get('password-confirmation')) {
            Template::alert('');
        }
        $user = new User($this->db);
        $user->save($name);
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
    }
}
