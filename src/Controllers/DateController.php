<?php

namespace App\Controllers;

use App\Template;
use App\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DateController extends Base
{
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function index(Request $request, Response $response, array $args)
    {
        $template = new Template('dates_index.twig');
        $template->year = (!empty($args['year'])) ? $args['year'] : null;
        $template->month = (!empty($args['month'])) ? $args['month'] : null;

        // Years.
        if (!$this->user->loaded()) {
            $sqlYears = "SELECT YEAR(date) AS year FROM items WHERE read_group = :g "
                . " GROUP BY YEAR(date) "
                . " ORDER BY YEAR(date) DESC";
            $paramsYears = ['g' => User::GROUP_PUBLIC];
        } else {
            $sqlYears = "SELECT YEAR(date) AS year FROM items "
                . " JOIN user_groups ug ON (ug.group=items.read_group AND ug.user=:uid) "
                . " GROUP BY YEAR(date) "
                . " ORDER BY YEAR(date) DESC ";
            $paramsYears = ['uid' => $this->user->getId()];
        }
        $yearsRes = $this->db->query($sqlYears, $paramsYears)->fetchAll();
        $years = [];
        foreach ($yearsRes as $year) {
            $years[] = ($year->year === null) ? 'Unknown' : $year->year;
        }
        $template->years = $years;

        // Months.
        // @TODO

        // Items.
        $paramsItems = [];
        if ($template->year) {
            $whereYear = ' WHERE YEAR(date) = :year ';
            $paramsItems['year'] = ($template->year == 'Unknown') ? null : $template->year;
        } else {
            $whereYear = '';
        }
        if (!$this->user->loaded()) {
            $sqlItems = "SELECT items.id FROM items WHERE read_group = :g $whereYear "
                . " ORDER BY items.date DESC LIMIT 20";
            $paramsItems['g'] = User::GROUP_PUBLIC;
        } else {
            $sqlItems = "SELECT items.id FROM items "
                . " JOIN user_groups ug ON (ug.group=items.read_group AND ug.user=:uid) "
                . " $whereYear ORDER BY items.date DESC LIMIT 20";
            $paramsItems['uid'] = $this->user->getId();
        }
        $items = $this->db->query($sqlItems, $paramsItems, '\\App\\Item');
        $template->items = $items;
        $template->title = 'Dates';

        $response->setContent($template->render());
        return $response;
    }
}
