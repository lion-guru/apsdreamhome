<?php

namespace App\Models;

class ProjectEnquiry extends Model
{
    public static $table = 'project_enquiries';

    protected array $fillable = [
        'project_code',
        'name',
        'email',
        'phone',
        'message',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'meta',
        'status',
        'created_at',
        'updated_at'
    ];

    /**
     * Create a new enquiry
     */
    public function createEnquiry(array $data)
    {
        try {
            if (isset($data['meta']) && \is_array($data['meta'])) {
                $data['meta'] = \json_encode($data['meta']);
            }

            $this->fill($data);
            if ($this->save()) {
                return $this->getKey();
            }
            return false;
        } catch (\Exception $e) {
            \error_log('ProjectEnquiry::createEnquiry error: ' . $e->getMessage());
            return false;
        }
    }
}
