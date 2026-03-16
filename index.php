<?php                                                                                                                                                                                  
  header('Access-Control-Allow-Origin: *');                       
  header('Content-Type: application/json');

  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      http_response_code(405);
      echo json_encode(['error' => 'Method not allowed']);
      exit;
  }

  $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
  $invitedBy = filter_input(INPUT_POST, 'invited_by', FILTER_SANITIZE_SPECIAL_CHARS);

  if (!$email) {
      http_response_code(400);
      echo json_encode(['error' => 'Invalid email']);
      exit;
  }

  // Get Mautic URL from environment variable
  $mauticUrl = getenv('MAUTIC_URL') ?: 'http://localhost:8080/form/submit';

  // Prepare form data for Mautic
  $postData = http_build_query([
      'email' => $email,
      'invited_by' => $invitedBy ?: '',  // Include invited_by field
  ]);

  $context = stream_context_create([
      'http' => [
          'method' => 'POST',
          'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
          'content' => $postData,
          'timeout' => 10
      ]
  ]);

  @file_get_contents($mauticUrl, false, $context);
  echo json_encode(['success' => true]);
