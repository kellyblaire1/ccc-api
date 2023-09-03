<?php

class Blogs extends Schema
{
    protected $query;

    private $table = BLOGS;
    private $stmt;
    private $result;
    private $num;
    private $row;
    private $msg = array();
    private $errMsg = array();
    private $data = array();
    private $response = array();

    private $rand = null;
    private $ip = null;
    private $created = null;

    private $dt;

    public function __construct()
    {}

    public function createTable()
    {

        $array = [
            $this->column('slug', $this->varchar(200), ''),
            $this->column('title', $this->varchar(200), ''),
            $this->column('category', $this->varchar(200), ''),
            $this->column('content', $this->longtext(), ''),
            $this->column('image', $this->varchar(200), 'DEFAULT \'image.png\''),
            $this->column('intro', $this->varchar(200), ''),
            $this->column('poster', $this->varchar(200), ''),
            $this->column('created', $this->datetime(), ''),
            $this->column('updated', $this->datetime(), ''),
        ];

        return $this->create($this->table, $array);
    }

    public function add($slug,$title,$intro,$content,$category,$poster,$image)
    {
        //create table
        if ($this->createTable()) {
            $data = [
                'slug' => $slug,
                'title' => $title,
                'intro' => $intro,
                'content' => $content,
                'category' => $category,
                'poster' => $poster,
                'image' => $image,
                'created' => $this->createdAt(),
            ];

            if(empty($title)) {
                return array('response' => 'error', 'message' => 'Please enter a title');
            } elseif ($this->checkSlugExists($slug) > 0) {
                return array('response' => 'error', 'message' => 'This blog post already exists. Change the title if it is different.');
            } else {
                if ($this->insert($this->table, $data)) {
                    http_response_code(200);
                            
                    return array('response' => 'success', 'message' => 'Post created successfully.');
                } else {
                    http_response_code(503);
                    return array('response' => 'error', 'message' => 'Error creating post.');
                }
            }
        }
    } 
    
    public function checkSlugExists($slug)
    {        

        $selector = ['*'];

        $conditionData = [$slug];

        $clause = "slug = ? LIMIT 1";

        return $this->selectCount($this->table, $selector, $conditionData, $clause);
    }  

    public function allPosts()
    {
        $selector = ["*"];

        $conditionData = [];

        $clause = "";

        return $this->select($this->table, $selector, $conditionData, $clause);
    }

    public function deletePost($id)
    {
        $selector = [];

        $conditionData = [$id];

        $clause = "id = ?";

        if ($this->delete($this->table, $conditionData, $clause)) {
            return array('response' => 'success', 'message' => 'Deleted');
        } else {
            return array('response' => 'error', 'message' => 'Not Deleted');
        }
    }

}