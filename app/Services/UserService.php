<?php
namespace App\Services;

use App\Repositories\UserRepository;
use Exception;
use DateTime;

class UserService {
    protected UserRepository $repository;

    public function __construct(UserRepository $repository) {
        $this->repository = $repository;
    }

    public function getRepository(): UserRepository {
        return $this->repository;
    }

    public function validateKyc(array $data, array $files) {
        $errors = [];
        $dob = $data['dob'];
        if (empty($dob)) {
            $errors['dob'] = 'Date of birth is required';
        } else {
            $dob_date = new DateTime($dob);
            $today = new DateTime();
            $age = $dob_date->diff($today)->y;
            if ($age < 18) {
                $errors['dob'] = 'You must be at least 18 years old';
            }
        }

        $gender = $data['gender'];
        if (empty($gender) || !in_array($gender, ['Male', 'Female', 'Other', 'Prefer not to say'])) {
            $errors['gender'] = 'Please select a valid gender';
        }

        $father_name = $data['father_name'];
        if (empty($father_name) || strlen($father_name) < 2) {
            $errors['father_name'] = "Father's name is required";
        }

        $mother_name = $data['mother_name'];
        if (empty($mother_name) || strlen($mother_name) < 2) {
            $errors['mother_name'] = "Mother's name is required";
        }

        $marital_status = $data['marital_status'];
        if (empty($marital_status) || !in_array($marital_status, ['Single', 'Married', 'Divorced', 'Widowed', 'Prefer not to say'])) {
            $errors['marital_status'] = 'Please select a valid marital status';
        }

        $nationality = $data['nationality'];
        if (empty($nationality)) {
            $errors['nationality'] = 'Nationality is required';
        }

        $occupation = $data['occupation'];
        if (empty($occupation)) {
            $errors['occupation'] = 'Occupation is required';
        }

        $source_of_income = $data['source_of_income'];
        if (empty($source_of_income)) {
            $errors['source_of_income'] = 'Source of income is required';
        }

        $annual_income = $data['annual_income'];
        if (empty($annual_income)) {
            $errors['annual_income'] = 'Annual income range is required';
        }

        $street = $data['street'];
        if (empty($street)) {
            $errors['street'] = 'Street address is required';
        }

        $city = $data['city'];
        if (empty($city)) {
            $errors['city'] = 'City is required';
        }

        $state = $data['state'];
        if (empty($state)) {
            $errors['state'] = 'State is required';
        }

        $pincode = $data['pincode'];
        if (empty($pincode) || !validate_pincode($pincode)) {
            $errors['pincode'] = 'Please enter a valid 6-digit pincode';
        }

        $id_type = $data['id_type'];
        $valid_id_types = ['Aadhaar', 'PAN', 'Passport', 'Voter ID', 'Driving License'];
        if (empty($id_type) || !in_array($id_type, $valid_id_types)) {
            $errors['id_type'] = 'Please select a valid document type';
        }

        $id_number = $data['id_number'];
        if (empty($id_number)) {
            $errors['id_number'] = 'Document number is required';
        }

        $has_profile_pic = !empty($data['has_profile_pic']);
        $has_id_front = !empty($data['has_id_front']);
        $has_id_back = !empty($data['has_id_back']);

        if (!isset($files['profile_picture']) || $files['profile_picture']['error'] !== UPLOAD_ERR_OK) {
            if (!$has_profile_pic) {
                $errors['profile_picture'] = 'Profile photo is required';
            }
        } else {
            $profile = $files['profile_picture'];
            if ($profile['size'] > MAX_UPLOAD_SIZE) {
                $errors['profile_picture'] = 'Profile photo must be less than 5MB';
            }
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $profile['tmp_name']);
            finfo_close($finfo);
            if (!in_array($mime, ALLOWED_IMAGE_TYPES)) {
                $errors['profile_picture'] = 'Profile photo must be JPG or PNG format';
            }
        }

        if (!isset($files['id_front']) || $files['id_front']['error'] !== UPLOAD_ERR_OK) {
            if (!$has_id_front) {
                $errors['id_front'] = 'Front image of identity document is required';
            }
        } else {
            $front = $files['id_front'];
            if ($front['size'] > MAX_UPLOAD_SIZE) {
                $errors['id_front'] = 'Front image must be less than 5MB';
            }
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $front['tmp_name']);
            finfo_close($finfo);
            if (!in_array($mime, ALLOWED_IMAGE_TYPES)) {
                $errors['id_front'] = 'Front image must be JPG or PNG format';
            }
        }

        if (!isset($files['id_back']) || $files['id_back']['error'] !== UPLOAD_ERR_OK) {
            if (!$has_id_back) {
                $errors['id_back'] = 'Back image of identity document is required';
            }
        } else {
            $back = $files['id_back'];
            if ($back['size'] > MAX_UPLOAD_SIZE) {
                $errors['id_back'] = 'Back image must be less than 5MB';
            }
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $back['tmp_name']);
            finfo_close($finfo);
            if (!in_array($mime, ALLOWED_IMAGE_TYPES)) {
                $errors['id_back'] = 'Back image must be JPG or PNG format';
            }
        }

        $account_type = $data['account_type'];
        if (empty($account_type) || !in_array($account_type, ['savings', 'current'])) {
            $errors['account_type'] = 'Please select a valid account type';
        }

        $initial_deposit = $data['initial_deposit'];
        $min_deposit = get_setting($this->repository->getDb(), 'min_initial_deposit', 500);
        if ($initial_deposit < $min_deposit) {
            $errors['initial_deposit'] = 'Initial deposit must be at least ' . CURRENCY_SYMBOL . $min_deposit;
        }

        $txn_pin = $data['txn_pin'];
        if (empty($txn_pin) || !preg_match('/^\d{4}$/', $txn_pin)) {
            $errors['txn_pin'] = 'Transaction PIN must be exactly 4 digits';
        }
        
        return $errors;
    }

    public function processKycUploads($files, $user_id, $existing_files) {
        if (!is_dir(UPLOAD_ID_DOCUMENTS_PATH)) {
            mkdir(UPLOAD_ID_DOCUMENTS_PATH, 0755, true);
        }
        $profile_upload_path = dirname(__DIR__, 2) . '/uploads/profiles/';
        if (!is_dir($profile_upload_path)) {
            mkdir($profile_upload_path, 0755, true);
        }

        $get_ext = function($file_arr) {
            if (isset($file_arr) && $file_arr['error'] === UPLOAD_ERR_OK) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file_arr['tmp_name']);
                finfo_close($finfo);
                return ($mime === 'image/jpeg') ? 'jpg' : 'png';
            }
            return '';
        };

        $profile_ext = $get_ext($files['profile_picture'] ?? null);
        $front_ext   = $get_ext($files['id_front'] ?? null);
        $back_ext    = $get_ext($files['id_back'] ?? null);

        $profile_filename = $profile_ext ? 'profile_' . $user_id . '_' . time() . '.' . $profile_ext : '';
        $front_filename = $front_ext ? 'id_front_' . $user_id . '_' . time() . '.' . $front_ext : '';
        $back_filename  = $back_ext ? 'id_back_' . $user_id . '_' . time() . '.' . $back_ext : '';

        $profile_destination = $profile_filename ? $profile_upload_path . $profile_filename : '';
        $front_destination = $front_filename ? UPLOAD_ID_DOCUMENTS_PATH . $front_filename : '';
        $back_destination  = $back_filename ? UPLOAD_ID_DOCUMENTS_PATH . $back_filename : '';

        if (isset($files['profile_picture']) && $files['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $uploaded = (defined('FINZO_TESTING') && FINZO_TESTING) ? copy($files['profile_picture']['tmp_name'], $profile_destination) : move_uploaded_file($files['profile_picture']['tmp_name'], $profile_destination);
            if (!$uploaded) {
                throw new Exception('Failed to upload profile photo');
            }
        }
        if (isset($files['id_front']) && $files['id_front']['error'] === UPLOAD_ERR_OK) {
            $uploaded = (defined('FINZO_TESTING') && FINZO_TESTING) ? copy($files['id_front']['tmp_name'], $front_destination) : move_uploaded_file($files['id_front']['tmp_name'], $front_destination);
            if (!$uploaded) {
                throw new Exception('Failed to upload front document image');
            }
        }
        if (isset($files['id_back']) && $files['id_back']['error'] === UPLOAD_ERR_OK) {
            $uploaded = (defined('FINZO_TESTING') && FINZO_TESTING) ? copy($files['id_back']['tmp_name'], $back_destination) : move_uploaded_file($files['id_back']['tmp_name'], $back_destination);
            if (!$uploaded) {
                throw new Exception('Failed to upload back document image');
            }
        }

        $profile_pic_path = (isset($files['profile_picture']) && $files['profile_picture']['error'] === UPLOAD_ERR_OK) 
            ? 'uploads/profiles/' . $profile_filename 
            : $existing_files['profile_picture'];

        $id_front_path = (isset($files['id_front']) && $files['id_front']['error'] === UPLOAD_ERR_OK) 
            ? 'uploads/id-documents/' . $front_filename 
            : $existing_files['id_front_path'];

        $id_back_path = (isset($files['id_back']) && $files['id_back']['error'] === UPLOAD_ERR_OK) 
            ? 'uploads/id-documents/' . $back_filename 
            : $existing_files['id_back_path'];
            
        return [
            'profile_pic_path' => $profile_pic_path,
            'id_front_path' => $id_front_path,
            'id_back_path' => $id_back_path,
            'front_destination' => $front_destination,
            'back_destination' => $back_destination
        ];
    }
}
