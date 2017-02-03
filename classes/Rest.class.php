<?php
    namespace BbRest;

    require_once(__DIR__ . "/../../vendor/autoload.php");
//    require_once(__DIR__."/../../vendor/pear-pear.php.net/HTTP_Request2/HTTP/Request2.php");
    require_once(__DIR__ . "/Availability.class.php");
    require_once(__DIR__ . "/Constants.class.php");
    require_once(__DIR__ . "/Contact.class.php");
    require_once(__DIR__ . "/Name.class.php");
    require_once(__DIR__ . "/Error.class.php");

    class Rest
    {

        public $constants = '';
        public $errors = [];

        public function not200($response)
        {
            //$error = new \Error();
            //        $error->label =  "Unexpected HTTP status: " ;
            //        $error->msg =  $response->getStatus() . ' ' .$response->getReasonPhrase();
            //        $error->data = json_decode($response->getBody());

            $error = new \Error(
                $msg = $response->getStatus() . ' ' . $response->getReasonPhrase(),
                $label = "Unexpected HTTP status: ",
                $data = json_decode($response->getBody())
            );

            $this->errors[] = $error;
        }

        public function authorize()
        {

            $constants = new Constants();
            $token = new Token();

            $request = new \HTTP_Request2($constants->HOSTNAME . $constants->AUTH_PATH, \HTTP_Request2::METHOD_POST);
            $request->setAuth($constants->KEY, $constants->SECRET, \HTTP_Request2::AUTH_BASIC);
            $request->setBody('grant_type=client_credentials');
            $request->setHeader('Content-Type', 'application/x-www-form-urlencoded');

            try {
                $response = $request->send();
                if (200 == $response->getStatus()) {
                    //print " Authorize Application...\n";
                    $token = json_decode($response->getBody());
                } else {
//                $error->label =  'Unexpected HTTP status: ' ;
//                $error->msg =  $response->getStatus() . ' ' .$response->getReasonPhrase();
//                $error->data = json_decode($response->getBody());
//                $this->errors[] = $error;
                    $this->not200($response);
                }
            } catch (\HTTP_Request2_Exception $e) {
                //$error->msg = $e->getMessage();
                $error = new \Error($e->getMessage());
                $this->errors[] = $error;
            }

            return $token;
        }

        public function createDatasource($access_token)
        {
            $constants = new Constants();
            $datasource = new Datasource();
            $error = new \Error();

            $request = new \HTTP_Request2($constants->HOSTNAME . $constants->DSK_PATH, \HTTP_Request2::METHOD_POST);
            $request->setHeader('Authorization', 'Bearer ' . $access_token);
            $request->setHeader('Content-Type', 'application/json');
            $request->setBody(json_encode($datasource));

            try {
                $response = $request->send();
                if (201 == $response->getStatus()) {
                    print "\n Create Datasource...\n";
                    $datasource = json_decode($response->getBody());
                } else {
                    print 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                        $response->getReasonPhrase();
                    $BbRestException = json_decode($response->getBody());
                    var_dump($BbRestException);
                }
            } catch (\HTTP_Request2_Exception $e) {
                $error->msg = $e->getMessage();
                $this->errors[] = $error;
            }

            return $datasource;
        }

        public function readDatasource($access_token, $dsk_id)
        {
            $constants = new Constants();
            $datasource = new Datasource();

            $request = new \HTTP_Request2($constants->HOSTNAME . $constants->DSK_PATH . '/' . $dsk_id, \HTTP_Request2::METHOD_GET);
            $request->setHeader('Authorization', 'Bearer ' . $access_token);

            try {
                $response = $request->send();
                if (200 == $response->getStatus()) {
                    print "\n Read Datasource...\n";
                    $datasource = json_decode($response->getBody());
                } else {
                    print 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                        $response->getReasonPhrase();
                    $BbRestException = json_decode($response->getBody());
                    var_dump($BbRestException);
                }
            } catch (\HTTP_Request2_Exception $e) {
                print 'Error: ' . $e->getMessage();
            }

            return $datasource;
        }

        public function updateDatasource($access_token, $dsk_id)
        {
            $constants = new Constants();
            $datasource = new Datasource();

            $datasource->id = $dsk_id;

            $request = new \HTTP_Request2($constants->HOSTNAME . $constants->DSK_PATH . '/' . $dsk_id, 'PATCH');
            $request->setHeader('Authorization', 'Bearer ' . $access_token);
            $request->setHeader('Content-Type', 'application/json');
            $request->setBody(json_encode($datasource));

            try {
                $response = $request->send();
                if (200 == $response->getStatus()) {
                    print "\n Update Datasource...\n";
                    $datasource = json_decode($response->getBody());
                } else {
                    print 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                        $response->getReasonPhrase();
                    $BbRestException = json_decode($response->getBody());
                    var_dump($BbRestException);
                }
            } catch (\HTTP_Request2_Exception $e) {
                print 'Error: ' . $e->getMessage();
            }

            return $datasource;
        }

        public function deleteDatasource($access_token, $dsk_id)
        {
            $constants = new Constants();

            $request = new \HTTP_Request2($constants->HOSTNAME . $constants->DSK_PATH . '/' . $dsk_id, \HTTP_Request2::METHOD_DELETE);
            $request->setHeader('Authorization', 'Bearer ' . $access_token);
            $request->setHeader('Content-Type', 'application/json');

            try {
                $response = $request->send();
                if (204 == $response->getStatus()) {
                    print "Datasource Deleted";
                } else {
                    print 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                        $response->getReasonPhrase();
                    $BbRestException = json_decode($response->getBody());
                    var_dump($BbRestException);

                    return FALSE;
                }
            } catch (\HTTP_Request2_Exception $e) {
                print 'Error: ' . $e->getMessage();

                return FALSE;
            }

            return TRUE;
        }

        public function createTerm($access_token, $dsk_id)
        {
            $constants = new Constants();
            $term = new Term();

            $term->dataSourceId = $dsk_id;
            $term->availability = new Availability();

            $request = new \HTTP_Request2($constants->HOSTNAME . $constants->TERM_PATH, \HTTP_Request2::METHOD_POST);
            $request->setHeader('Authorization', 'Bearer ' . $access_token);
            $request->setHeader('Content-Type', 'application/json');
            $request->setBody(json_encode($term));

            try {
                $response = $request->send();
                if (201 == $response->getStatus()) {
                    print "\n Create Term...\n";
                    $term = json_decode($response->getBody());
                } else {
                    print 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                        $response->getReasonPhrase();
                    $BbRestException = json_decode($response->getBody());
                    var_dump($BbRestException);
                }
            } catch (\HTTP_Request2_Exception $e) {
                print 'Error: ' . $e->getMessage();
            }

            return $term;
        }

        public function readTerm($access_token, $term_id)
        {
            $constants = new Constants();
            $term = new Term();

            $request = new \HTTP_Request2($constants->HOSTNAME . $constants->TERM_PATH . '/' . $term_id, \HTTP_Request2::METHOD_GET);
            $request->setHeader('Authorization', 'Bearer ' . $access_token);

            try {
                $response = $request->send();
                if (200 == $response->getStatus()) {
                    print "\n Read Term...\n";
                    $datasource = json_decode($response->getBody());
                } else {
                    print 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                        $response->getReasonPhrase();
                    $BbRestException = json_decode($response->getBody());
                    var_dump($BbRestException);
                }
            } catch (\HTTP_Request2_Exception $e) {
                print 'Error: ' . $e->getMessage();
            }

            return $term;
        }

        public function updateTerm($access_token, $dsk_id, $term_id)
        {
            $constants = new Constants();
            $term = new Term();

            $term->id = $term_id;
            $term->dataSourceId = $dsk_id;

            $request = new \HTTP_Request2($constants->HOSTNAME . $constants->TERM_PATH . '/' . $term_id, 'PATCH');
            $request->setHeader('Authorization', 'Bearer ' . $access_token);
            $request->setHeader('Content-Type', 'application/json');
            $request->setBody(json_encode($term));

            try {
                $response = $request->send();
                if (200 == $response->getStatus()) {
                    print "\n Update Term...\n";
                    $datasource = json_decode($response->getBody());
                } else {
                    print 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                        $response->getReasonPhrase();
                    $BbRestException = json_decode($response->getBody());
                    var_dump($BbRestException);
                }
            } catch (\HTTP_Request2_Exception $e) {
                print 'Error: ' . $e->getMessage();
            }

            return $term;
        }

        public function deleteTerm($access_token, $term_id)
        {
            $constants = new Constants();

            $request = new \HTTP_Request2($constants->HOSTNAME . $constants->TERM_PATH . '/' . $term_id, \HTTP_Request2::METHOD_DELETE);
            $request->setHeader('Authorization', 'Bearer ' . $access_token);
            $request->setHeader('Content-Type', 'application/json');

            try {
                $response = $request->send();
                if (204 == $response->getStatus()) {
                    print "Term Deleted";
                } else {
                    print 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                        $response->getReasonPhrase();
                    $BbRestException = json_decode($response->getBody());
                    var_dump($BbRestException);

                    return FALSE;
                }
            } catch (\HTTP_Request2_Exception $e) {
                print 'Error: ' . $e->getMessage();

                return FALSE;
            }

            return TRUE;
        }

        public function createCourse($access_token, $dsk_id, $term_id)
        {
            $constants = new Constants();
            $course = new Course();

            $course->dataSourceId = $dsk_id;
            $course->termId = $term_id;
            $course->availability = new Availability();

            $request = new \HTTP_Request2($constants->HOSTNAME . $constants->COURSE_PATH, \HTTP_Request2::METHOD_POST);
            $request->setHeader('Authorization', 'Bearer ' . $access_token);
            $request->setHeader('Content-Type', 'application/json');
            $request->setBody(json_encode($course));

            try {
                $response = $request->send();
                if (201 == $response->getStatus()) {
                    print "\n Create Course...\n";
                    $course = json_decode($response->getBody());
                } else {
                    print 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                        $response->getReasonPhrase();
                    $BbRestException = json_decode($response->getBody());
                    var_dump($BbRestException);
                }
            } catch (\HTTP_Request2_Exception $e) {
                print 'Error: ' . $e->getMessage();
            }

            return $course;
        }

        public function readCourse($access_token, $course_id)
        {
            $constants = new Constants();
            $course = new Course();

            $request = new \HTTP_Request2($constants->HOSTNAME . $constants->COURSE_PATH . '/' . $course_id, \HTTP_Request2::METHOD_GET);
            $request->setHeader('Authorization', 'Bearer ' . $access_token);

            try {
                $response = $request->send();
                if (200 == $response->getStatus()) {
                    //print "\n Read Course...\n";
                    $course = json_decode($response->getBody());
                } else {
                    print 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                        $response->getReasonPhrase();
                    $BbRestException = json_decode($response->getBody());
                    var_dump($BbRestException);
                }
            } catch (\HTTP_Request2_Exception $e) {
                print 'Error: ' . $e->getMessage();
            }

            return $course;
        }

        public function updateCourse($access_token, $dsk_id, $course_id, $course_uuid, $course_created, $termId)
        {
            $constants = new Constants();
            $course = new Course();

            $course->id = $course_id;
            $course->uuid = $course_uuid;
            $course->created = $course_created;
            $course->dataSourceId = $dsk_id;
            $course->termId = $termId;

            $request = new \HTTP_Request2($constants->HOSTNAME . $constants->COURSE_PATH . '/' . $course_id, 'PATCH');
            $request->setHeader('Authorization', 'Bearer ' . $access_token);
            $request->setHeader('Content-Type', 'application/json');
            $request->setBody(json_encode($course));

            try {
                $response = $request->send();
                if (200 == $response->getStatus()) {
                    print "\n Update Course...\n";
                    $course = json_decode($response->getBody());
                } else {
                    print 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                        $response->getReasonPhrase();
                    $BbRestException = json_decode($response->getBody());
                    var_dump($BbRestException);
                }
            } catch (\HTTP_Request2_Exception $e) {
                print 'Error: ' . $e->getMessage();
            }

            return $course;
        }

        public function deleteCourse($access_token, $course_id)
        {
            $constants = new Constants();

            $request = new \HTTP_Request2($constants->HOSTNAME . $constants->COURSE_PATH . '/' . $course_id, \HTTP_Request2::METHOD_DELETE);
            $request->setHeader('Authorization', 'Bearer ' . $access_token);
            $request->setHeader('Content-Type', 'application/json');

            try {
                $response = $request->send();
                if (204 == $response->getStatus()) {
                    print "Course Deleted";
                } else {
                    print 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                        $response->getReasonPhrase();
                    $BbRestException = json_decode($response->getBody());
                    var_dump($BbRestException);

                    return FALSE;
                }
            } catch (\HTTP_Request2_Exception $e) {
                print 'Error: ' . $e->getMessage();

                return FALSE;
            }

            return TRUE;
        }

        public function createUser($access_token, $dsk_id)
        {
            $constants = new Constants();
            $user = new User();

            $user->dataSourceId = $dsk_id;
            $user->availability = new Availability();
            $user->name = new Name();
            $user->contact = new Contact();

            $request = new \HTTP_Request2($constants->HOSTNAME . $constants->USER_PATH, \HTTP_Request2::METHOD_POST);
            $request->setHeader('Authorization', 'Bearer ' . $access_token);
            $request->setHeader('Content-Type', 'application/json');
            $request->setBody(json_encode($user));

            try {
                $response = $request->send();
                if (201 == $response->getStatus()) {
                    print "\n Create User...\n";
                    $user = json_decode($response->getBody());
                } else {
                    print 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                        $response->getReasonPhrase();
                    $BbRestException = json_decode($response->getBody());
                    var_dump($BbRestException);
                }
            } catch (\HTTP_Request2_Exception $e) {
                print 'Error: ' . $e->getMessage();
            }

            return $user;
        }

        public function readUser($access_token, $user_id)
        {
            $constants = new Constants();
            $user = new User();

            $request = new \HTTP_Request2($constants->HOSTNAME . $constants->USER_PATH . '/' . $user_id, \HTTP_Request2::METHOD_GET);
            $request->setHeader('Authorization', 'Bearer ' . $access_token);

            try {
                $response = $request->send();
                if (200 == $response->getStatus()) {
                    // print "\n Read User...\n";
                    $user = json_decode($response->getBody());
                } else {
                    print 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                        $response->getReasonPhrase();
                    $BbRestException = json_decode($response->getBody());
                    var_dump($BbRestException);
                }
            } catch (\HTTP_Request2_Exception $e) {
                print 'Error: ' . $e->getMessage();
            }

            return $user;
        }

        public function updateUser($access_token, $dsk_id, $user_id, $user_uuid, $user_created)
        {
            $constants = new Constants();
            $user = new User();

            $user->id = $user_id;
            $user->uuid = $user_uuid;
            $user->created = $user_created;
            $user->dataSourceId = $dsk_id;

            $request = new \HTTP_Request2($constants->HOSTNAME . $constants->USER_PATH . '/' . $user_id, 'PATCH');
            $request->setHeader('Authorization', 'Bearer ' . $access_token);
            $request->setHeader('Content-Type', 'application/json');
            $request->setBody(json_encode($user));

            try {
                $response = $request->send();
                if (200 == $response->getStatus()) {
                    print "\n Update User...\n";
                    $user = json_decode($response->getBody());
                } else {
                    print 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                        $response->getReasonPhrase();
                    $BbRestException = json_decode($response->getBody());
                    var_dump($BbRestException);
                }
            } catch (\HTTP_Request2_Exception $e) {
                print 'Error: ' . $e->getMessage();
            }

            return $user;
        }

        public function deleteUser($access_token, $user_id)
        {
            $constants = new Constants();

            $request = new \HTTP_Request2($constants->HOSTNAME . $constants->USER_PATH . '/' . $user_id, \HTTP_Request2::METHOD_DELETE);
            $request->setHeader('Authorization', 'Bearer ' . $access_token);
            $request->setHeader('Content-Type', 'application/json');

            try {
                $response = $request->send();
                if (204 == $response->getStatus()) {
                    print "User Deleted";
                } else {
                    print 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                        $response->getReasonPhrase();
                    $BbRestException = json_decode($response->getBody());
                    var_dump($BbRestException);

                    return FALSE;
                }
            } catch (\HTTP_Request2_Exception $e) {
                print 'Error: ' . $e->getMessage();

                return FALSE;
            }

            return TRUE;
        }


        public function createMembership($access_token, $dsk_id, $course_id, $user_id)
        {
            $constants = new Constants();
            $membership = new Membership();

            $membership->dataSourceId = $dsk_id;
            $membership->availability = new Availability();
            $membership->userId = $user_id;
            $membership->courseId = $course_id;


            $request = new \HTTP_Request2($constants->HOSTNAME . $constants->COURSE_PATH . '/' . $course_id . '/users/' . $user_id, \HTTP_Request2::METHOD_PUT);
            $request->setHeader('Authorization', 'Bearer ' . $access_token);
            $request->setHeader('Content-Type', 'application/json');
            $request->setBody(json_encode($membership));

            try {
                $response = $request->send();
                if (201 == $response->getStatus()) {
                    print "\n Create Membership...\n";
                    $membership = json_decode($response->getBody());
                } else {
                    print 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                        $response->getReasonPhrase();
                    $BbRestException = json_decode($response->getBody());
                    var_dump($BbRestException);
                }
            } catch (\HTTP_Request2_Exception $e) {
                print 'Error: ' . $e->getMessage();
            }

            return $membership;
        }

        public function readMembership($access_token, $course_id, $user_id)
        {
            $constants = new Constants();
            $membership = new Membership();

            $request = new \HTTP_Request2($constants->HOSTNAME . $constants->COURSE_PATH . '/' . $course_id . '/users/' . $user_id, \HTTP_Request2::METHOD_GET);
            $request->setHeader('Authorization', 'Bearer ' . $access_token);

            try {
                $response = $request->send();
                if (200 == $response->getStatus()) {
                    //print "\n Read Membership...\n";
                    $membership = json_decode($response->getBody());
                } else {
                    print 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                        $response->getReasonPhrase();
                    $BbRestException = json_decode($response->getBody());
                    var_dump($BbRestException);
                }
            } catch (\HTTP_Request2_Exception $e) {
                print 'Error: ' . $e->getMessage();
            }

            return $membership;
        }

        public function updateMembership($access_token, $dsk_id, $course_id, $user_id, $membership_created)
        {
            $constants = new Constants();
            $membership = new Membership();

            $membership->dataSourceId = $dsk_id;
            $membership->userId = $user_id;
            $membership->courseId = $course_id;
            $membership->created = $membership_created;

            $request = new \HTTP_Request2($constants->HOSTNAME . $constants->COURSE_PATH . '/' . $course_id . '/users/' . $user_id, 'PATCH');
            $request->setHeader('Authorization', 'Bearer ' . $access_token);
            $request->setHeader('Content-Type', 'application/json');
            $request->setBody(json_encode($membership));

            try {
                $response = $request->send();
                if (200 == $response->getStatus()) {
                    print "\n Update Membership...\n";
                    $membership = json_decode($response->getBody());
                } else {
                    print 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                        $response->getReasonPhrase();
                    $BbRestException = json_decode($response->getBody());
                    var_dump($BbRestException);
                }
            } catch (\HTTP_Request2_Exception $e) {
                print 'Error: ' . $e->getMessage();
            }

            return $membership;
        }

        public function deleteMembership($access_token, $course_id, $user_id)
        {
            $constants = new Constants();

            $request = new \HTTP_Request2($constants->HOSTNAME . $constants->COURSE_PATH . '/' . $course_id . '/users/' . $user_id, \HTTP_Request2::METHOD_DELETE);
            $request->setHeader('Authorization', 'Bearer ' . $access_token);
            $request->setHeader('Content-Type', 'application/json');

            try {
                $response = $request->send();
                if (204 == $response->getStatus()) {
                    print "Membership Deleted";
                } else {
                    print 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                        $response->getReasonPhrase();
                    $BbRestException = json_decode($response->getBody());
                    var_dump($BbRestException);

                    return FALSE;
                }
            } catch (\HTTP_Request2_Exception $e) {
                print 'Error: ' . $e->getMessage();

                return FALSE;
            }

            return TRUE;
        }
    }
