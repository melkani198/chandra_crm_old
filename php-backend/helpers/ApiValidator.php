<?php

class ApiValidator {
    public static function validateCampaignPayload($data, $isUpdate = false) {
        $errors = [];
        $allowedCampaignTypes = ['preview', 'progressive', 'predictive', 'inbound'];
        $allowedStatus = ['active', 'paused', 'stopped', 'draft'];

        if (!$isUpdate && empty($data['name'])) {
            $errors['name'][] = 'Campaign name is required';
        }

        if (isset($data['name']) && strlen(trim((string) $data['name'])) < 3) {
            $errors['name'][] = 'Campaign name must be at least 3 characters';
        }

        if (isset($data['campaign_type']) && !in_array($data['campaign_type'], $allowedCampaignTypes, true)) {
            $errors['campaign_type'][] = 'Invalid campaign type';
        }

        if (isset($data['status']) && !in_array($data['status'], $allowedStatus, true)) {
            $errors['status'][] = 'Invalid campaign status';
        }

        if (isset($data['max_dial_time']) && (!is_numeric($data['max_dial_time']) || (int) $data['max_dial_time'] < 10)) {
            $errors['max_dial_time'][] = 'max_dial_time must be a number >= 10';
        }

        if (isset($data['pacing']) && !is_numeric($data['pacing'])) {
            $errors['pacing'][] = 'pacing must be numeric';
        }

        if (isset($data['wrap_up_time']) && (!is_numeric($data['wrap_up_time']) || (int) $data['wrap_up_time'] < 0)) {
            $errors['wrap_up_time'][] = 'wrap_up_time must be a number >= 0';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    public static function validateDialPayload($data) {
        $errors = [];
        $allowedModes = ['preview', 'progressive', 'predictive', 'manual'];

        if (empty($data['phone'])) {
            $errors['phone'][] = 'Phone number is required';
        } elseif (!preg_match('/^[0-9+]{7,20}$/', (string) $data['phone'])) {
            $errors['phone'][] = 'Phone number format is invalid';
        }

        if (isset($data['mode']) && !in_array($data['mode'], $allowedModes, true)) {
            $errors['mode'][] = 'Invalid dial mode';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    public static function validateAgentStatusPayload($data) {
        $errors = [];
        $allowedActions = ['login', 'logout', 'ready', 'break', 'manual_on', 'manual_off', 'on_call', 'wrap_up'];

        if (empty($data['action'])) {
            $errors['action'][] = 'action is required';
        } elseif (!in_array($data['action'], $allowedActions, true)) {
            $errors['action'][] = 'Invalid action';
        }

        if (isset($data['extension']) && !preg_match('/^[0-9]{2,10}$/', (string) $data['extension'])) {
            $errors['extension'][] = 'Extension format is invalid';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
