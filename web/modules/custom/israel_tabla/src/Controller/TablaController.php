<?php

namespace Drupal\israel_tabla\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class TablaController extends ControllerBase
{

  /**
   * @var SessionInterface
   */
  private $session;

  public function __construct(SessionInterface $session)
  {
    $this->session = $session;
  }

  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('session')
    );
  }

  public function tabla() {

    $build = [];

    $build[] = $this->formBuilder()->getForm('Drupal\israel_tabla\Form\FiltroForm');

    $filters = $this->session->get('israel_tabla_filters', []);

    $query = $this->entityTypeManager()->getStorage('node')->getQuery();
    $query->sort('created', 'DESC');

    if (isset($filters['titulo'])) {
      if(!empty($filters['titulo'])){
        $query->condition('title', $filters['titulo'], 'CONTAINS');
      }
    }

    if (isset($filters['tipo'])) {
      if('none' != $filters['tipo']){
        $query->condition('type', [$filters['tipo']], 'IN');
      }
    }

    $query->pager(2);

    $result = $query->execute();

    /** @var NodeInterface[] $nodes */
    $nodes = $this->entityTypeManager()->getStorage('node')->loadMultiple($result);

    $filas = [];
    foreach ($nodes as $node) {
      $filas[] = [
        'data' => [
          $node->toLink(),
          $node->bundle(),
          $node->getOwner()->toLink(),
          date('d/m/Y H:i:s', $node->get('created')->value),
        ],
      ];
    }

    $cabeceras = [
      'Titulo',
      'Tipo',
      'Autor',
      'Fecha de creacion',
    ];

    $tabla = [
      '#type' => 'table',
      '#header' => $cabeceras,
      '#rows' => $filas,
    ];

    $build[] = $tabla;

    $build[] = ['#type' => 'pager'];

    return $build;
  }

}
