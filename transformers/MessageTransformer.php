<?php


namespace Transformers;


use League\Fractal\TransformerAbstract;

class MessageTransformer extends TransformerAbstract
{
    public function transform($message)
    {
        return [
            'id' => (int) $message['id'],
            'subject' => $message['subject'],
            'message' => $message['message'],
            'owner' => $message['owner'],
            'task_id' => $message['task_id']
        ];
    }
}
