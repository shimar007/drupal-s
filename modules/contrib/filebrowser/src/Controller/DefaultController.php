<?php

namespace Drupal\filebrowser\Controller;

use Drupal;
use Drupal\Core\Ajax\AfterCommand;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\AlertCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Render\HtmlResponse;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\Core\Url;
use Drupal\filebrowser\Filebrowser;
use Drupal\filebrowser\FilebrowserManager;
use Drupal\filebrowser\Services\FilebrowserValidator;
use Drupal\filebrowser\Services\Common;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use ZipArchive;

/**
 * Default controller for the filebrowser module.
 */
class DefaultController extends ControllerBase {

  /**
   * @var \Drupal\filebrowser\FilebrowserManager $filebrowserManager
   */
  protected $filebrowserManager;
  /**
   * @var \Drupal\filebrowser\Services\FilebrowserValidator
   */
  protected $validator;

  /**
   * @var \Drupal\filebrowser\Services\Common
   */
  protected $common;

  /**
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * DefaultController constructor.
   *
   * @param FilebrowserManager $filebrowserManager
   * @param FilebrowserValidator $validator
   * @param Common $common
   *
   */
  public function __construct(FilebrowserManager $filebrowserManager, FilebrowserValidator $validator, Common $common, FileUrlGeneratorInterface $fileUrlGenerator) {
    $this->filebrowserManager = $filebrowserManager;
    $this->validator = $validator;
    $this->common = $common;
    $this->fileUrlGenerator = $fileUrlGenerator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('filebrowser.manager'),
      $container->get('filebrowser.validator'),
      $container->get('filebrowser.common'),
      $container->get('file_url_generator')
    );
  }

  /**
   * Callback for
   * route: filebrowser.page_download
   * path: filebrowser/download/{fid}
   * @param int $fid Id of the file selected in the download link
   * @return RedirectResponse | StreamedResponse
   */
  public function pageDownload($fid) {
    /* @var NodeInterface $node **/
    $node_content = $this->common->nodeContentLoad($fid);
    // If $fid doesn't point to a valid file, $node_content is FALSE.
    if (!$node_content) {
      throw new NotFoundHttpException();
    }
    $file_data = unserialize($node_content['file_data']);
    $filebrowser = new Filebrowser($node_content['nid']);

    // Download method is 'public' and the uri is public://
    // we will send the browser to the file location.
    // todo:
    // RedirectResponse needs a relative path so we will convert the full url into a relative path
    // This is done here, but should be moved to a better place in Common
    $file_path = $this->fileUrlGenerator->transformRelative($file_data->url);
    if ($filebrowser->downloadManager == 'public' && StreamWrapperManager::getScheme($file_data->uri) == 'public') {
      return new RedirectResponse($file_path);
    }
    // we will stream the file
    else {
      // load the node containing the file so we can check
      // for the access rights
      // User needs "view" permission on the node to download the file
      $node = Node::load($node_content['nid']);
      if (isset($node) && $node->access('view')) {
        // Stream the file
        $file = $file_data->uri;
        // in case you need the container
        //$container = $this->container;
        $response = new StreamedResponse(function () use ($file) {
          $handle = fopen($file, 'r') or exit("Cannot open file $file");
          while (!feof($handle)) {
            $buffer = fread($handle, 1024);
            echo $buffer;
            flush();
          }
          fclose($handle);
        });
        $response->headers->set('Content-Type', $file_data->mimetype);
        $content_disposition = $filebrowser->forceDownload ? 'attachment' : 'inline';
        $response->headers->set('Content-Disposition', $content_disposition . '; filename="' . $file_data->filename . '";');
        return $response;

      }
      elseif (isset($node)) {
        throw new AccessDeniedHttpException();
      }
      else {
        throw new NotFoundHttpException();
      }
    }
  }

  /**
   * @param int $nid
   * @param int $query_fid In case of a sub folder, the fid of the sub folder
   * @param string $op - The operation called by the submit button ('upload', 'delete')
   * @param string $method - Defines if Ajax should be used
   * @param string|null $fids A string containing the field id's of the files
   * to be processed.
   *
   * @return array | AjaxResponse
   */
  public function actionFormSubmitAction($nid, $query_fid, $op, $method, $fids = NULL) {
    // $op == archive does not use a form
    if ($op == 'archive') {
      return $this->actionArchive($nid, $fids);
    }

    // continue for buttons needing a form
    // Determine the requested form name
    $op = ucfirst($op);
    $form_name = 'Drupal\filebrowser\Form\\' . $op . 'Form';
    //debug($form_name);
    $form = Drupal::formBuilder()->getForm($form_name, $nid, $query_fid, $fids, $method == 'ajax');

    // If JS enabled
    if ($method == 'ajax' && $op <> 'Archive') {

      // Create an AjaxResponse.
      $response = new AjaxResponse();
      // Remove old error in case they exist.
      $response->addCommand(new RemoveCommand('#filebrowser-form-action-error'));
      // Remove slide-downs if they exist.
      $response->addCommand(new RemoveCommand('.form-in-slide-down'));
      // Insert event details after event.
      $response->addCommand(new AfterCommand('#form-action-actions-wrapper', $form));
      return $response;
    }
    else {
      return $form;
    }
  }

  public function inlineDescriptionForm($nid, $query_fid, $fids) {
    return \Drupal::formBuilder()->getForm('Drupal\filebrowser\Form\InlineDescriptionForm', $nid, $query_fid, $fids);
  }

  /**
   * @function
   * Creates a ZIP archive from local or S3 files and directories.
   *
   * @param string $fids
   *   Comma-separated list of file entity IDs.
   * @param int $nid
   *   Node id of the node calling the archive
   *
   * @return Response
   *    A file download response on success or a redirect with an error message on failure.
   */
  public function actionArchive($nid, $fids): Response {
    $fid_array = explode(',', $fids);
    $itemsToArchive = $this->common->nodeContentLoadMultiple($fid_array);
    $file_system = \Drupal::service('file_system');

    $archive_path = 'public://archive_' . uniqid() . '.zip';
    $zip_path = $file_system->realpath($archive_path);

    $archive = new \ZipArchive();
    $created = $archive->open($zip_path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

    if ($created !== TRUE) {
      \Drupal::logger('filebrowser')->error('Cannot create archive at @path (error code: @code)', [
        '@path' => $zip_path,
        '@code' => $created,
      ]);
      \Drupal::messenger()->addError('Cannot create archive at @path (error code: @code)');
      $route = $nid
        ? Url::fromRoute('entity.node.canonical', ['node' => $nid])
        : Url::fromRoute('<front>');
      return new RedirectResponse($route->toString());
    }

    foreach ($itemsToArchive as $item) {
      $file_data = unserialize($item['file_data']);
      $uri = $file_data->uri;
      $scheme = \Drupal::service('stream_wrapper_manager')->getScheme($uri);
      $is_local = $file_system->realpath($uri) !== false;
      $filename = $file_data->filename;
      if ($is_local) {
        if ($file_data->type === 'file') {
          $file_path = $file_system->realpath($uri);
          $archive->addFile($file_path, $filename);
        }
        elseif ($file_data->type === 'dir') {
          $dirPath = $file_system->realpath($uri);
          if (!$dirPath || !is_dir($dirPath)) {
            \Drupal::logger('filebrowser')->error('Directory not found or not accessible: @path', ['@path' => $uri]);
            continue;
          }

          $rootDirName = basename($dirPath);
          $archive->addEmptyDir($rootDirName);

          $iterator = new \RecursiveDirectoryIterator($dirPath, \RecursiveDirectoryIterator::SKIP_DOTS);
          $dirFiles = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST);
          $baseLen = strlen($dirPath) + 1;

          foreach ($dirFiles as $fileInfo) {
            $realPath = $fileInfo->getRealPath();
            $relativePath = $rootDirName . '/' . substr($realPath, $baseLen);

            if ($fileInfo->isDir()) {
              $archive->addEmptyDir($relativePath);
            } elseif ($fileInfo->isFile()) {
              $archive->addFromString($relativePath, file_get_contents($realPath));
            }
          }
        }
      }
      else {
        // Remote storage (e.g., S3 or other cloud)
        if ($file_data->type === 'file') {
          $temp_uri = 'temporary://' . $filename;
          if ($file_system->copy($uri, $temp_uri, 1)) {
            $file_path = $file_system->realpath($temp_uri);
            $archive->addFile($file_path, $filename);
          }
          else {
            \Drupal::logger('filebrowser')->error(t('Failed to copy remote file @file', ['@file' => $uri]));
          }
        }
        else {
          \Drupal::logger('filebrowser')->info('Skipping remote directory @uri', ['@uri' => $uri]);
          \Drupal::messenger()->addWarning(t('Archiving cloud directory @filename is not supported. Open the directory and select the files manually.', ['@filename' => $filename]));
        }
      }
    }

    $archive->close();

    if (!file_exists($zip_path)) {
      \Drupal::logger('filebrowser')->error(t('ZIP file was not created: @path', ['@path' => $zip_path]));
      \Drupal::messenger()->addError(t('ZIP file was not created.'));
      $route = $nid
        ? Url::fromRoute('entity.node.canonical', ['node' => $nid])
        : Url::fromRoute('<front>');
      return new RedirectResponse($route->toString());
    }

    $response = new BinaryFileResponse($zip_path);
    $response->deleteFileAfterSend(true);
    $response->trustXSendfileTypeHeader();
    $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    $response->prepare(Request::createFromGlobals());
    return $response;
  }

  public function noItemsError() {
    $error = $this->t('You didn\'t select any item');

    // Create an AjaxResponse.
    $response = new AjaxResponse();
    // Remove old events
    $response->addCommand(new RemoveCommand('#filebrowser-form-action-error'));
    $response->addCommand(new RemoveCommand('.form-in-slide-down'));
    // Insert event details after event.
    // $response->addCommand(new AfterCommand('#form-action-actions-wrapper', $html));
    $response->addCommand(new AlertCommand($error));
    return $response;
  }

}
