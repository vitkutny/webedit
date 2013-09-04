<?php

namespace CMS\Admin\Page;

use Nette\Application\UI\Form;
use CMS\Component\Form\FormRenderer;
use Nette\Application\BadRequestException;

final class PagePresenter extends BasePresenter {

    /**
     * @persistent int|NULL
     */
    public $id;
    private $page;

    public function renderAdd() {
        $this->menu->breadcrumbAdd('Add new page');
    }

    public function actionEdit($id) {
        $this->page = $this->pageRepository->getPage($id);
        if (!$this->page) {
            throw new BadRequestException;
        }
    }

    public function renderEdit() {
        $this->menu->breadcrumbAdd('Edit page: ' . $this->page->node->title);
    }

    protected function createComponentPageAddForm() {
        $form = new Form();
        $form->setRenderer(new FormRenderer);

        //$form['node'] = new NodeFormContainer();
        $node = $form->addContainer('node');
        $node->addText('title', 'Title')->setRequired();
        $parent = $this->menu->getParentNodeSelectData('front');
        $node->addSelect('node_id', 'Parent node', $parent)->setRequired();

        //$form['page'] = new PageFormContainer();
        $page = $form->addContainer('page');
        $page->addTextArea('content', 'Content')->setRequired();

        $form->onSuccess[] = $this->processPageForm;
        $form->addSubmit('add', 'Add page');
        return $form;
    }

    protected function createComponentPageEditForm() {
        $form = new Form();
        $form->setRenderer(new FormRenderer);

        //$form['node'] = new NodeFormContainer($this->page->node);
        $node = $form->addContainer('node');
        $node->addText('title', 'Title')->setRequired();
        $parent = $this->menu->getParentNodeSelectData('front', $this->page->node);
        if ($parent) {
            $node->addSelect('node_id', 'Parent node', $parent)->setRequired();
        }
        $node->setDefaults($this->page->node);

        //$form['page'] = new PageFormContainer($this->page);
        $page = $form->addContainer('page');
        $page->addTextArea('content', 'Content')->setRequired();
        $page->setDefaults($this->page);

        $form->onSuccess[] = $this->processPageForm;
        $form->addSubmit('edit', 'Edit page');
        if ($parent) {
            $form->addSubmit('remove', 'Remove page')->setAttribute('class', 'btn-danger');
        }
        return $form;
    }

    /**
     * @param Form $form
     */
    public function processPageForm(Form $form) {
        if ($this->id AND !$this->page) {
            throw new BadRequestException;
        }

        if ($this->id) {
            if ($form->getHttpData('remove')) {
                $this->pageRepository->removePage($this->page);
                $this->redirect('Home:view');
            } else {
                $this->pageRepository->editPage($this->page, $form->getValues(TRUE));
                $this->redirect('this');
            }
        } else {
            $page = $this->pageRepository->addPage($form->getValues(TRUE));
            $this->redirect($page->node->link_admin, array('id' => $page->node->link_id));
        }
    }

}