<?php
class Mbp15_battery_repair_program_model extends \Model
{

    public function __construct($serial = '')
    {
        parent::__construct('id', 'wifi'); //primary key, tablename
        $this->rs['id'] = 0;
        $this->rs['serial_number'] = $serial;
        $this->rs['datecheck'] = '';
        $this->rs['eligibility'] = '';
        
        // Schema version, increment when creating a db migration
        $this->schema_version = 0;

        // Add indexes
        $this->idx[] = array('datecheck');
        $this->idx[] = array('eligibility');

        // Create table if it does not exist
       //$this->create_table();
        
        if ($serial) {
            $this->retrieve_record($serial);
        }
        
        $this->serial = $serial;
    }

    
    /**
     * Get mbp15_battery_repair_program state for widget
     *
     **/
    public function get_mbp15_battery_repair_program_state()
    {
        $sql = "SELECT COUNT(CASE WHEN eligibiltiy = 'E00-Eligible' THEN 1 END) AS eligible,
				COUNT(CASE WHEN eligibiltiy = 'E01-Ineligible' THEN 1 END) AS ineligible,
				COUNT(CASE WHEN eligibiltiy = 'E99-ProcessingError' THEN 1 END) AS processing_error,
				COUNT(CASE WHEN eligibiltiy = 'FE01-EmptySerial' THEN 1 END) AS empty_serial,
				COUNT(CASE WHEN eligibiltiy = 'FE02-InvalidSerial' THEN 1 END) AS invalid_serial,
				COUNT(CASE WHEN eligibiltiy = 'Err1-UnexpectedResponse' THEN 1 END) AS unexpected_response,
				COUNT(CASE WHEN eligibiltiy = 'Err2-NotQueried-InvalidGuidLength' THEN 1 END) AS invalid_guid_length,
				COUNT(CASE WHEN eligibiltiy = 'Err3-NotQueried-InvalidSerialLength' THEN 1 END) AS invalid_serial_length,
				COUNT(CASE WHEN eligibiltiy = 'Msg1-IneligibleModel' THEN 1 END) AS ineligible_model
				FROM mbp15_battery_repair_program
				LEFT JOIN reportdata USING(serial_number)
				".get_machine_group_filter();
        return current($this->query($sql));
    }
    
    public function process($data)
    {
        // Translate strings to db fields
        $translate = array(
            'Eligibility: ' => 'eligibility',
            'LastCheck: ' => 'datecheck');

        // Parse data
        foreach (explode("\n", $data) as $line) {
            // Translate standard entries
            foreach ($translate as $search => $field) {
                if (strpos($line, $search) === 0) {
                    $value = substr($line, strlen($search));
                    
                    $this->$field = $value;
                    break;
                }
            }
        } //end foreach explode lines
        $this->save();
    }
}
