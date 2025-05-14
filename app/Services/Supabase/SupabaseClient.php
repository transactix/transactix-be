<?php

namespace App\Services\Supabase;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class SupabaseClient
{
    protected string $url;
    protected string $key;
    protected string $secret;
    protected Client $client;

    /**
     * Create a new Supabase client instance.
     *
     * @param string $url The Supabase project URL
     * @param string $key The Supabase API key (anon key for public operations)
     * @param string $secret The Supabase service role key (for admin operations)
     */
    public function __construct(string $url, string $key, string $secret)
    {
        $this->url = $url;
        $this->key = $key;
        $this->secret = $secret;
        $this->client = new Client([
            'base_uri' => $this->url,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * Execute a query on the specified table.
     *
     * @param string $table The table name
     * @param array $options Query options (select, where, order, limit, etc.)
     * @param bool $useServiceRole Whether to use the service role key (admin access)
     * @return array|null The query results or null on error
     */
    public function query(string $table, array $options = [], bool $useServiceRole = false): ?array
    {
        $apiKey = $useServiceRole ? $this->secret : $this->key;
        $endpoint = '/rest/v1/' . $table;
        
        $queryParams = [];
        
        // Handle select columns
        if (isset($options['select'])) {
            $queryParams['select'] = is_array($options['select']) 
                ? implode(',', $options['select']) 
                : $options['select'];
        }
        
        // Handle where conditions
        if (isset($options['where'])) {
            foreach ($options['where'] as $column => $value) {
                $queryParams[$column] = 'eq.' . $value;
            }
        }
        
        // Handle order
        if (isset($options['order'])) {
            $queryParams['order'] = $options['order'];
        }
        
        // Handle limit
        if (isset($options['limit'])) {
            $queryParams['limit'] = $options['limit'];
        }
        
        // Handle offset
        if (isset($options['offset'])) {
            $queryParams['offset'] = $options['offset'];
        }
        
        try {
            $response = $this->client->request('GET', $endpoint, [
                'headers' => [
                    'apikey' => $apiKey,
                    'Authorization' => 'Bearer ' . $apiKey,
                ],
                'query' => $queryParams,
            ]);
            
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Supabase query error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Insert data into the specified table.
     *
     * @param string $table The table name
     * @param array $data The data to insert
     * @param bool $useServiceRole Whether to use the service role key (admin access)
     * @return array|null The inserted data or null on error
     */
    public function insert(string $table, array $data, bool $useServiceRole = false): ?array
    {
        $apiKey = $useServiceRole ? $this->secret : $this->key;
        $endpoint = '/rest/v1/' . $table;
        
        try {
            $response = $this->client->request('POST', $endpoint, [
                'headers' => [
                    'apikey' => $apiKey,
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Prefer' => 'return=representation',
                ],
                'json' => $data,
            ]);
            
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Supabase insert error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update data in the specified table.
     *
     * @param string $table The table name
     * @param array $data The data to update
     * @param array $conditions The conditions for the update
     * @param bool $useServiceRole Whether to use the service role key (admin access)
     * @return array|null The updated data or null on error
     */
    public function update(string $table, array $data, array $conditions, bool $useServiceRole = false): ?array
    {
        $apiKey = $useServiceRole ? $this->secret : $this->key;
        $endpoint = '/rest/v1/' . $table;
        
        $queryParams = [];
        
        // Build conditions
        foreach ($conditions as $column => $value) {
            $queryParams[$column] = 'eq.' . $value;
        }
        
        try {
            $response = $this->client->request('PATCH', $endpoint, [
                'headers' => [
                    'apikey' => $apiKey,
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Prefer' => 'return=representation',
                ],
                'query' => $queryParams,
                'json' => $data,
            ]);
            
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Supabase update error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete data from the specified table.
     *
     * @param string $table The table name
     * @param array $conditions The conditions for the delete
     * @param bool $useServiceRole Whether to use the service role key (admin access)
     * @return bool Whether the delete was successful
     */
    public function delete(string $table, array $conditions, bool $useServiceRole = false): bool
    {
        $apiKey = $useServiceRole ? $this->secret : $this->key;
        $endpoint = '/rest/v1/' . $table;
        
        $queryParams = [];
        
        // Build conditions
        foreach ($conditions as $column => $value) {
            $queryParams[$column] = 'eq.' . $value;
        }
        
        try {
            $response = $this->client->request('DELETE', $endpoint, [
                'headers' => [
                    'apikey' => $apiKey,
                    'Authorization' => 'Bearer ' . $apiKey,
                ],
                'query' => $queryParams,
            ]);
            
            return $response->getStatusCode() === 200 || $response->getStatusCode() === 204;
        } catch (GuzzleException $e) {
            Log::error('Supabase delete error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Execute a raw SQL query.
     *
     * @param string $query The SQL query
     * @param array $params The query parameters
     * @return array|null The query results or null on error
     */
    public function rpc(string $function, array $params = []): ?array
    {
        $endpoint = '/rest/v1/rpc/' . $function;
        
        try {
            $response = $this->client->request('POST', $endpoint, [
                'headers' => [
                    'apikey' => $this->secret,
                    'Authorization' => 'Bearer ' . $this->secret,
                ],
                'json' => $params,
            ]);
            
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Supabase RPC error: ' . $e->getMessage());
            return null;
        }
    }
}
