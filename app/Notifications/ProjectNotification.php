<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ProjectNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    public Project $project;
    public string $type;

    /**
     * Create a new notification instance.
     */
    public function __construct(Project $project, string $type)
    {
        $this->project = $project;
        $this->type = $type;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        if ($this->type === 'project.created') {
            $title = 'Proyek Baru: ' . $this->project->project_code;
            $message = 'Proyek baru untuk kapal ' . ($this->project->ship->name ?? '-') . ' telah dibuat.';
        } else {
            $title = 'Proyek Selesai: ' . $this->project->project_code;
            $message = 'Proyek untuk kapal ' . ($this->project->ship->name ?? '-') . ' telah selesai.';
        }

        return [
            'title' => $title,
            'message' => $message,
            'url' => route('project.show', $this->project->unique_id),
        ];
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => $this->toArray($notifiable)['title'],
            'message' => $this->toArray($notifiable)['message'],
            'url' => $this->toArray($notifiable)['url'],
        ]);
    }
}
