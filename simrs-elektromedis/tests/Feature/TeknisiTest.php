<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TeknisiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_display_teknisi_index_page()
    {
        $response = $this->get('/teknisi');

        $response->assertStatus(200);
        $response->assertSee('Teknisi');
    }

    /** @test */
    public function it_can_create_a_teknisi()
    {
        $data = [
            'nama_teknisi' => 'John Doe',
            'alamat' => '123 Main St',
            'no_hp' => '08123456789',
            'photo' => null,
        ];

        $response = $this->post('/teknisi', $data);

        $response->assertRedirect('/teknisi');
        $this->assertDatabaseHas('teknisi', $data);
    }

    /** @test */
    public function it_can_edit_a_teknisi()
    {
        $teknisi = \App\Models\Teknisi::factory()->create();

        $data = [
            'nama_teknisi' => 'Jane Doe',
            'alamat' => '456 Elm St',
            'no_hp' => '08987654321',
            'photo' => null,
        ];

        $response = $this->put('/teknisi/' . $teknisi->id, $data);

        $response->assertRedirect('/teknisi');
        $this->assertDatabaseHas('teknisi', $data);
    }

    /** @test */
    public function it_can_delete_a_teknisi()
    {
        $teknisi = \App\Models\Teknisi::factory()->create();

        $response = $this->delete('/teknisi/' . $teknisi->id);

        $response->assertRedirect('/teknisi');
        $this->assertDeleted($teknisi);
    }
}