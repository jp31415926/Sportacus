<?php
namespace Cerad\Bundle\ProjectBundle\Action\ProjectGame\Export;

use Psr\Http\Message\ServerRequestInterface as Request;

class ExportForm
{
  protected $data;
  protected $valid  = false;
  protected $posted = false;

  public function setData($data)
  {
    $this->data = $data;
  }
  public function getData()
  {
    return $this->data;
  }
  public function isValid() {
    return $this->valid;
  }
  public function handleRequest(Request $request)
  {
    if ($request->getMethod() !== 'POST') {
      return;
    }
    $this->posted = true;

    $post = $request->getParsedBody();
    $this->data['project'] = $post['project'];

    $this->valid = true;
  }
  public function render()
  {
    return <<<TYPEOTHER
<form action="/project-game/export" method="POST" enctype="application/x-www-form-urlencoded">
<label>Project
  <select name="project">
    <option value="20" selected>Fall 2015</option>
    <option value="15" selected>Fall 2014 - Area 5C Tournament</option>
    <option value="13" selected>Fall 2014 - R0498 Tournament</option>
    <option value="14" selected>Fall 2014 - R0894 Tournament</option>
  </select>
</label><br/>
  <input type="submit" value="Export" name="export"/>
</form>
TYPEOTHER;
  }
}