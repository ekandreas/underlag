<?php

namespace App\Http\Controllers;

use App\Inbox;
use App\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MailgunController extends Controller
{
    protected $request;
    protected $data;
    protected $message;

    public function catchAll(Request $request)
    {
        $this->request = $request;

        $this->handleData()
            ->assignTeam()
            ->createMessage()
            ->saveAttachments()
            ->performOCR();

        return (string)$this->message->_id;
    }

    private function handleData()
    {
        $this->data['form'] = $this->request->all();

        $this->data['subject'] = $this->data['form']['Subject'];
        $this->data['sender'] = $this->data['form']['Sender'];

        $this->data['html'] = $this->data['form']['stripped-html'];
        $this->data['text'] = $this->data['form']['stripped-text'];

        $this->data['attachments'] = [];

        return $this;
    }

    private function assignTeam()
    {
        $this->data['team'] = 0;
        return $this;
    }

    private function createMessage()
    {
        $message = new Message($this->data);
        $message->save();
        $this->message = $message;
        return $this;
    }

    private function saveAttachments()
    {
        $attachments = $this->message->attachments;
        $fileCount = isset($this->data['form']['attachment-count']) ? (int)$this->data['form']['attachment-count']:0;
        $messageId = (string)$this->message->_id;
        if($fileCount) {
            for($i=0; $i<$fileCount; $i++) {
                $attachmentFile = "attachment-" . ($i+1);
                if($this->request->hasFile($attachmentFile)) {
                    $file = $this->request->$attachmentFile;
                    $attachments[] = [
                        'path' => $file->store("messages/{$messageId}"),
                        'name' => $file->getClientOriginalName(),
                        'size' => $file->getClientSize(),
                    ];
                }
            }
            $this->message->attachments = $attachments;
            $this->message->save();
        }
        return $this;
    }

    private function performOCR()
    {
        return $this;
    }

}
