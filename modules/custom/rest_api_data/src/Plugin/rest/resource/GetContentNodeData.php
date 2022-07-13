<?php

namespace Drupal\rest_api_data\Plugin\rest\resource;

use Drupal\node\Entity\Node;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\Core\Session\AccountProxyInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\Component\Serialization\Json;
use \Symfony\Component\DomCrawler\Crawler;
use Drupal\Core\Datetime;
use Drupal\Core\Entity;
use Drupal\Core\Entity\Sql;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBuilderInterface;

/**
 * Annotation for get method
 *
 * @RestResource(
 *   id = "drupal_s_get_content_node",
 *   label = @Translation("Get Content Node"),
 *   serialization_class = "",
 *   uri_paths = {
 *     "canonical" = "/api/get-content-node",
 *     "create" = "/api/get-content-node"
 *   }
 * )
 */

class GetContentNodeData extends ResourceBase {
    
    /**
    * A current user instance.
    *
    * @var \Drupal\Core\Session\AccountProxyInterface
    */
    protected $currentUser;
    /**
     * Constructs a Drupal\rest\Plugin\ResourceBase object.
     *
     * @param array $configuration
     *   A configuration array containing information about the plugin instance.
     * @param string $plugin_id
     *   The plugin_id for the plugin instance.
     * @param mixed $plugin_definition
     *   The plugin implementation definition.
     * @param array $serializer_formats
     *   The available serialization formats.
     * @param \Psr\Log\LoggerInterface $logger
     *   A logger instance.
     * @param \Drupal\Core\Session\AccountProxyInterface $current_user
     *   A current user instance.
     */
    public function __construct(
        array $configuration,
        $plugin_id,
        $plugin_definition,
        array $serializer_formats,
        LoggerInterface $logger,
        AccountProxyInterface $current_user)
    {
        parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
        $this->currentUser = $current_user;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
    {
        return new static(
        $configuration,
        $plugin_id,
        $plugin_definition,
        $container->getParameter('serializer.formats'),
        $container->get('logger.factory')->get('rest'),
        $container->get('current_user')
        );
    }

    /**
     * Responds to GET requests.
     *
     * Returns node data entity.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     *   Throws exception expected.
    */
    public function get() {

        try {

            // Use current user after pass authentication to validate access.
            if (!$this->currentUser->hasPermission('administer site content')) {
                // Display the default access denied page.
                throw new AccessDeniedHttpException('Access Denied.');
            }

            //get node id
            $nid = \Drupal::request()->query->get('nid');

            //result array
            $result_array = array();

            if($nid) {

                //define data array
                $data_array = array();

                //get node data
                $node = Node::load($nid);

                //get node title data
                $title = $node->title->value;

                //get node body content
                $field_body_content = $node->field_body_content;

                //initialize loop
                $i = 0;

                //initialize array
                $field_body_content_array = array();

                //looping through 
                foreach ($field_body_content as $body_content) {
                    
                    //get paragraph entity reference
                    $referenced_body_content = $body_content->entity;

                    //get entity bunble type
                    $paragraph_type = $referenced_body_content->bundle();

                    //get header paragrah data
                    if($paragraph_type == 'home_page_header_') {

                        //get heading for paragraph
                        $field_body_content_array[$i]['field_heading'] = $referenced_body_content->field_main_heading->value;

                        //increment counter
                        $i++;
                    }

                    //get about us paragraph data
                    if($paragraph_type == 'about_us') {
                        
                        //get heading for paragraph
                        $field_body_content_array[$i]['field_heading'] = $referenced_body_content->field_heading->value;

                        //increment counter
                        $i++;
                    }
                }
                
                //get node meta tags
                $field_meta_tags = $node->field_meta_tags;

                //setting all the 
                $data_array['title'] = $title;
                $data_array['field_body_content'] = $field_body_content_array;
                $data_array['field_meta_tags'] = $field_meta_tags;

                //data message
                $data_message['data'] = [$data_array];
                $data_message['message'] = "Node data is successfully responsed back";
            }
            else {
                //data message
                $data_message['data'] = "No Node ID passed, please check your API";
            }

            //unsetting get parameters
            unset($nid);

            return new ResourceResponse($data_message);
        }
        catch (Exception $e) {

            //data message error
            $data_message['data'] = $e->getMessage();

            return new ResourceResponse($data_message);
        }
        
    }
}