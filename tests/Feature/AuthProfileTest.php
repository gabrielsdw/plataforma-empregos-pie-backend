<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_can_register_with_resume_saved_to_local_public_storage(): void
    {
        Storage::fake('public');

        $response = $this->post('/api/auth/register/seeker', [
            'name' => 'Ana Souza',
            'email' => 'ana@example.com',
            'phone' => '(11) 98765-4321',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'resume' => UploadedFile::fake()->create('resume.pdf', 120, 'application/pdf'),
        ]);

        $user = User::query()->where('email', 'ana@example.com')->firstOrFail();

        $response
            ->assertCreated()
            ->assertJsonPath('data.user.name', 'Ana Souza')
            ->assertJsonPath('data.user.resume_original_name', 'resume.pdf')
            ->assertJsonPath('data.user.resume_url', Storage::disk('public')->url($user->resume_path));

        $this->assertNotNull($user->resume_path);
        $this->assertSame('resume.pdf', $user->resume_original_name);
        Storage::disk('public')->assertExists($user->resume_path);
    }

    public function test_candidate_can_update_profile_and_replace_resume(): void
    {
        Storage::fake('public');

        $oldPath = UploadedFile::fake()->create('old-resume.pdf', 80, 'application/pdf')
            ->store('resumes', 'public');

        $candidate = User::factory()->create([
            'role' => 'candidate',
            'name' => 'Maria Silva',
            'email' => 'maria@example.com',
            'phone' => '(11) 98888-7777',
            'resume_path' => $oldPath,
            'resume_original_name' => 'old-resume.pdf',
        ]);

        $token = auth('api')->login($candidate);

        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->post('/api/auth/profile', [
                'name' => 'Maria da Silva',
                'email' => 'maria.silva@example.com',
                'phone' => '(11) 99999-0000',
                'resume' => UploadedFile::fake()->create('curriculo-final.docx', 120, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
            ]);

        $candidate->refresh();

        $response
            ->assertOk()
            ->assertJsonPath('data.name', 'Maria da Silva')
            ->assertJsonPath('data.email', 'maria.silva@example.com')
            ->assertJsonPath('data.phone', '(11) 99999-0000')
            ->assertJsonPath('data.resume_original_name', 'curriculo-final.docx')
            ->assertJsonPath('data.resume_url', Storage::disk('public')->url($candidate->resume_path));

        $this->assertNotSame($oldPath, $candidate->resume_path);
        $this->assertSame('curriculo-final.docx', $candidate->resume_original_name);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($candidate->resume_path);
    }

    public function test_candidate_can_update_profile_without_replacing_resume(): void
    {
        $candidate = User::factory()->create([
            'role' => 'candidate',
            'name' => 'Joao Silva',
            'email' => 'joao@example.com',
            'phone' => '(11) 97777-6666',
            'resume_path' => 'resumes/existing-file.pdf',
            'resume_original_name' => 'curriculo-joao.pdf',
        ]);

        $token = auth('api')->login($candidate);

        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/auth/profile', [
                'name' => 'Joao Pedro Silva',
                'email' => 'joao.pedro@example.com',
                'phone' => '(11) 96666-5555',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.resume_path', 'resumes/existing-file.pdf')
            ->assertJsonPath('data.resume_original_name', 'curriculo-joao.pdf')
            ->assertJsonPath('data.resume_url', Storage::disk('public')->url('resumes/existing-file.pdf'));

        $candidate->refresh();

        $this->assertSame('resumes/existing-file.pdf', $candidate->resume_path);
        $this->assertSame('curriculo-joao.pdf', $candidate->resume_original_name);
    }

    public function test_candidate_can_download_saved_resume(): void
    {
        Storage::fake('public');

        $resumePath = UploadedFile::fake()->create('curriculo-ana.pdf', 120, 'application/pdf')
            ->store('resumes', 'public');

        $candidate = User::factory()->create([
            'role' => 'candidate',
            'resume_path' => $resumePath,
            'resume_original_name' => 'curriculo-ana.pdf',
        ]);

        $token = auth('api')->login($candidate);

        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->get('/api/auth/resume');

        $response->assertOk();
        $response->assertHeader('content-disposition', 'attachment; filename=curriculo-ana.pdf');
    }

}
