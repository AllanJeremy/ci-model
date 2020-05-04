<?php defined('BASEPATH') or exit('No direct script access allowed');

//Model override
class MY_Model extends CI_Model
{
    protected $table_name;

	function __construct($table_name='')
	{
        $this->table_name = $table_name;
		$this->load->database();
		// $this->db->save_queries = FALSE;
	}

	// Generates a random 64 bit string
	private function _generate_random_string($prepend='',$append='',$length=NULL)
	{
		$length = empty($length) ? ID_STRING_LENGTH : $length;
	
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-';
		$characters_length = strlen($characters);
		$randomString = isset($prepend) ? $prepend : '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $characters_length - 1)];
		}
		return $randomString.@$append;
	}

	/** Get a table field  
	 * @param string $table_name The name of the table the column/field belongs to
	 * @param string $field_name The column/field name we want to get a reference to. Defaults to all columns in the specified table
	 * @return string Returns a complete sql based concatenation of the table and field/column name 
	*/
	protected function get_table_field(string $table_name,string $field_name='*'):string
	{
		return $table_name.'.'.$field_name;
	}

	// Checks if an id exists, returns true if it does and false if it doesn't
	private function _id_exists($table_name,$id_column,$generated_id)
	{
		$_field_id = $this->get_table_field($table_name,$id_column);
		$this->db->select($_field_id);

		// Check if the id exists in the table we are trying to add it to
		$this->db->where(
			array(
				$_field_id => $generated_id
			)
		);
		$item_found = $this->db->get($table_name)->row_object();

		return !empty($item_found);
	}

	/** Generate a random string id for the table specified 
	 * @param string $table_name The name of the table we want to check for the id
	 * @param mixed $id_column Name of the id column we are going to be checking
	*/
	public function generate_string_id($table_name,$id_column='id')
    {		
		$generated_id = $this->_generate_random_string();
		
		$item_exists = $this->_id_exists($table_name,$id_column,$generated_id);

		// If id exists ~ try genrating again (recursively)
		if($item_exists)
		{//! Big O of log(N) ~ Consider finding optimization, possibly use dynamic programming
			$generated_id = $this->generate_string_id($table_name,$id_column);
		}
		else
		{
			return $generated_id;
		}
	}

	/**  Loops through multiple filters and generates relevant search filters 
	 * @description Using this to allow for duplicate search query entries, eg. Using one search string to get multiple items
	*/
	protected function use_search_filters($search_filters)
	{
		if(empty($search_filters))
		{
			return;
		}
		
		// Only use search filters if they were found
		for($i=0; $i<count($search_filters); $i++)
		{
			$current_filter = $search_filters[$i];

			// This allows for starting the query with a 'AND LIKE' which in turn allows for correct searching 
			if($i === 0)
			{
				$this->db->like($current_filter);
			}
			else
			{
				$this->db->or_like($current_filter);
			}
		}
		// var_dump($this->db->get_compiled_select());
	}

	// Uses filter if the limit is not empty
	protected function use_filters($filters)
	{
		if(!empty($filters))
		{
			$this->db->where($filters);
		}
	}

	// Uses limit if the limit is not empty
	protected function use_limit($limit,$offset=0)
	{
		if(!empty($limit))
		{
			$this->db->limit($limit,$offset);
		}
    }
    
    /* 
        CRUD
    */
    // Determine whether `WHERE` or `LIKE` should be used
    private function _get_filter_query($filters,$is_strict) # Facade pattern in use
    {
        // Use `WHERE` for strict matching
        if($is_strict)
        {
            $this->use_filters($filters);
        }
        else // Use `LIKE` for loose matching
        {
            $this->use_search_filters($filters);
        }
    }

    // Create
    public function create($data)
    {
        return $this->db->insert($this->table_name,$data);
    }

    // Insert batch ~ multiple records at once
    public function create_batch($data)
    {
        return $this->db->insert_batch($this->table_name,$data);
    }

    // Read
    public function read($filters,$limit=NULL,$offset=0,$is_strict=TRUE)
    {
        $this->_get_filter_query($filters,$is_strict);
        $this->use_limit($limit,$offset);

        return $this->db->get($this->table_name);
    }

    // Update ~ optionally use `LIKE`, or `WHERE` ~ defaults to `WHERE`
    public function update($filters,$data,$is_strict=TRUE)
    {
        $this->_get_filter_query($filters,$is_strict);
        return $this->db->update($this->table_name,$data);
    }

    // Delete
    public function delete($filters,$is_strict=TRUE)
    {
        $this->_get_filter_query($filters,$is_strict);
        return $this->db->delete($this->table_name);
    }
}
