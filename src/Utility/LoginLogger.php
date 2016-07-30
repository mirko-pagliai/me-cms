<?php
/**
 * This file is part of MeCms.
 *
 * MeCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @see         MeCms\Utility\SitemapBuilder
 */
namespace MeCms\Utility;

use Cake\Filesystem\File;
use Cake\I18n\Time;

class LoginLogger {
    /**
     * File resource
     * @var object
     */
    protected $file;

    /**
     * Construct
     * @param int $id User ID
     * @uses $file
     */
    public function __construct($id) {
        $this->file = new File(TMP.'login'.DS.'user_'.$id.'.log', TRUE);
    }
    
    /**
     * Gets data
     * @return array
     * @uses $file
     */
    public function get() {
        $data = $this->file->read();
        
        if(empty($data)) {
            return [];
        }
        
        //Unserializes
        return unserialize($data);
    }
    
    /**
     * Saves data
     * @uses get()
     * @uses $file
     */
    public function save() {
        //Gets existing data
        $data = $this->get();
        
        $agent = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT');
        $ip = get_client_ip();
        
        //Clears the first log (last in order of time), if it has been saved 
        //  less than an hour ago and the user agent and the IP address are
        //  the same
        if(!empty($data[0]) &&
            (new Time($data[0]->time))->modify('+1 hour')->isFuture() && 
            $data[0]->agent === $agent && 
            $data[0]->ip === $ip) {
                unset($data[0]);
        }
        
        //Adds log for current request
        array_unshift($data, (object) am([
            'ip' => get_client_ip(),
            'time' => new Time(),
        ], parse_user_agent(), compact('agent')));
        
        //Keeps only the first 20 records
        $data = array_slice($data, 0, 20);
        
        //Serializes
        $data = serialize($data);
        
        return $this->file->write($data);
    }
}
