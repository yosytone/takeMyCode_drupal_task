<?php

namespace Drupal\custom_field_type\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class MultiSelectController.
 */
class MultiSelectController extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new MultiSelectController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Factory method to create MultiSelectController instance.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container interface.
   *
   * @return \Drupal\custom_field_type\Controller\MultiSelectController
   *   The created MultiSelectController instance.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Returns a JSON response containing.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response.
   */
  public function getTags(Request $request) {
    // Get the 'q' parameter from the request.
    $q = $request->query->get('q');
    
    // If 'q' parameter is empty, return an empty JSON response.
    if (empty($q)) {
      return new JsonResponse([
          'results' => [],
          'pagination' => ['more' => false]
      ]);
    }

    // Get the 'page' parameter from the request or set default value to 1.
    $page = $request->query->get('page') ?: 1;

    // Log the search query.
    \Drupal::logger('custom_Request2')->notice($q);

    // Load taxonomy terms matching the search query.
    $term_storage = $this->entityTypeManager->getStorage('taxonomy_term');
    $query = $term_storage->getQuery()
      ->condition('vid', 'tags')
      ->condition('name', $q . '%', 'LIKE')
      ->count()
      ->accessCheck(FALSE); // Disable access check.

    $total_terms = $query->execute();

    // Define pagination limit and calculate offset.
    $limit = 8;
    $offset = ($page - 1) * $limit;

    // Load taxonomy terms with pagination.
    $query = $term_storage->getQuery()
      ->condition('vid', 'tags')
      ->condition('name',  $q . '%', 'LIKE')
      ->range($offset, $limit)
      ->accessCheck(FALSE); // Disable access check.

    $tids = $query->execute();
    $terms = $term_storage->loadMultiple($tids);

    // Prepare options array in the required JSON format.
    $options = [];
    foreach ($terms as $term) {
      $options[] = [
        'id' => $term->id(),
        'text' => $term->label(),
      ];
    }

    // Calculate if there are more pages available.
    $more = ($offset + count($terms)) < $total_terms;

    // Return JSON response with options and pagination information.
    return new JsonResponse([
      'results' => $options,
      'pagination' => ['more' => $more]
    ]);
  }
}