<?php

namespace Maleo\AdminBundle\Controller;

use Doctrine\ORM\Query;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;

class AdminController extends Controller
{
    /**
     * Attributes used by all templates
     */
    protected $baseRoute;
    protected $entityName;

    /**
     * Attributes used by list template
     */
    private $list_item_per_page = 15;
    protected $filters = [];
    protected $filtersType = [];

    /**
     * Define the column which will be dispay in the List Action
     * @var array
     */
    private $listColumn = [];

    /**
     * Configuration for the List Action
     * @var array
     */
    protected $listConf = [];

    /**
     * Define all the available actions for the List Action
     * @var array
     */
    private $listActions = [];

    protected $listTitle;

    private $defaultSortList = ['defaultSortFieldName' => 'a.dateUpdate', 'defaultSortDirection' => 'desc'];

    protected $entity;
    protected $templatePath = 'CRAdminBundle::';
    protected $page;
    protected $request;

    /**
     * Available options for list actions
     * @var array
     */
    private $availableListActionOpt = [
        'route' ,
        'label',
        'routeParam' ,
        'icon',
        'template',
    ];

    /**
     * Available option for column
     * @var array
     */
    private $availableListColumnOpt = [
        'label',
        'template',
    ];

    /**
     * @var Form
     */
    protected $filterForm = null;

    /**
     * @param $page
     * @param null $template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function renderList($page, $template = null)
    {
        $user = $this->getUser();

        $tpl = (null === $template) ? $this->templatePath.'list.html.twig' : $template;

        if ($this->filterForm) {
            $this->filterForm = $this->createForm($this->filterForm);
        }

        $qb = $this->buildFilterQuery();

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $qb->getQuery(),
            $page,
            $this->list_item_per_page,
            $this->defaultSortList
        );

        return $this->render($tpl, array(
            'user' => $user,
            'nbPerPage' => $this->list_item_per_page,
            'page' => $page,
            'form' => ($this->filterForm) ? $this->filterForm->createView() : null,
            'baseRoute' => $this->baseRoute,
            'entityName' => $this->entityName,
            'list' => [
                'records' => $pagination,
                'fields' => $this->listColumn,
                'parameters' => $this->listConf,
                'actions' => $this->listActions,
            ],
            'actionsList' => $this->actionsList(),
            'listTitle' => $this->listTitle,
        ));
    }


    public function buildFilterQuery()
    {
        $em = $this->getDoctrine()->getManager();

        $qb = $em->createQueryBuilder()->select('a')->from($this->entity, 'a');

        if (null !== $this->filterForm) {
            $this->filterForm->handleRequest($this->request);
            if ($this->filterForm->isSubmitted() && $this->filterForm->isValid()) {
                $data = $this->filterForm->getData();
                foreach ($data as $name => $value) {
                    if (substr($name, 0, 7) === 'filter_') {
                        $this->filtersType[substr($name, 7)] = $value;
                    } else {
                        if (null !== $value) {
                            $this->filters[] = [$name, $value];
                        }
                    }
                }
            }
        }

        foreach ($this->filters as $key => $filter) {
            $filterType = '=';
            if (isset($this->filtersType[$filter[0]])) {
                $filterType = $this->filtersType[$filter[0]];
                $value = $filter[1];

                if ($this->filtersType[$filter[0]] === 'contains') {
                    $value = '%'.$filter[1].'%';
                    $filterType = 'like';
                }
            }

            if (0 === $key) {
                $qb->where('a.'.$filter[0].' '.$filterType.' :a_'.$filter[0])
                    ->setParameter('a_'.$filter[0], $value);
            } else {
                $qb->andWhere('a.'.$filter[0].' '.$filterType.' :a_'.$filter[0])
                    ->setParameter('a_'.$filter[0], $value);
            }
        }

        return $qb;
    }

    /**
     * @param integer $nb
     * @throws \Exception
     */
    protected function setListItemPerPage($nb)
    {
        if (!is_numeric($nb) || $nb < 0) {
            throw new \Exception ('setListItemPerPage method expect a positive integer, "'.$nb.'"" given');
        }

        if (is_float($nb)) {
            $nb = ceil($nb);
        }

        $this->list_item_per_page = (integer) $nb;
    }

    /**
     * Add one action to the list actions array
     *
     * @param string $name
     * @param array $conf
     * @throws \Exception
     */
    protected function addListAction($name, array $conf)
    {
        foreach ($conf as $key => $value) {
            if (!in_array($key,$this->availableListActionOpt)) {
                throw new \Exception ('"'.$key.'" is not an allowed option for action '.$name.'. Available options are: '.implode(', ',$this->availableListActionOpt));
            }
        }

        $this->listActions[$name] = $conf;
    }

    /**
     * Set the list actions
     *
     * @param array $actions
     * @throws \Exception
     */
    protected function setListActions(array $actions)
    {
        $this->listActions = [];

        foreach ($actions as $name => $conf) {
            $this->addListAction($name, $conf);
        }
    }

    /**
     * @param $name
     * @param array $conf
     * @throws \Exception
     */
    protected function addListColumn($name, array $conf)
    {
        foreach ($conf as $key => $value) {
            if (!in_array($key,$this->availableListActionOpt)) {
                throw new \Exception ('"'.$key.'" is not an allowed option for column'.$name.'. Available options are: '.implode(', ',$this->availableListColumnOpt));
            }
        }

        $this->listColumn[$name] = $conf;
    }

    /**
     * @param array $columns
     * @throws \Exception
     */
    protected function setListColumn(array $columns)
    {
        $this->listColumn = [];

        foreach ($columns as $name => $conf) {
            $this->addListColumn($name, $conf);
        }
    }

    protected function setSortList(array $sort)
    {
        $this->defaultSortList = $sort;
    }

    /**
     * Return an array of [route,label] which will be use to build a list of action displayed on the top right of the list page
     * @return array
     */
    protected function actionsList()
    {
        return [];
    }
}
