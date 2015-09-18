<?php
/**
 * Created by PhpStorm.
 * User: evgeny
 * Date: 08.09.15
 * Time: 21:30
 */

namespace ApiBundle\Entity;


interface IProject {

    const STATUS_ACTIVE = 1;
    const STATUS_DRAFT = 0;

    public function getName();
    public function setName($name);

    public function getAlias();
    public function setAlias($alias);

    public function getTypeId();
    public function setTypeId($typeId);

    public function setStatusId($statusId);
    public function getStatusId();

}