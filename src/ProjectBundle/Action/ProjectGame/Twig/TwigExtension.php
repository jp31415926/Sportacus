<?php

namespace Cerad\Bundle\ProjectBundle\Action\ProjectGame\Twig;

use Scheduler\SchBundle\Entity\Game;
use Cerad\Bundle\ProjectBundle\Entity\ProjectGameTeam;
use Cerad\Bundle\ProjectBundle\Entity\ProjectGameOfficial;

class TwigExtension extends \Twig_Extension {

	public function getName() {
		return 'sportacus_project_game_twig_extension';
	}

	public function getFilters() {
		return [
				new \Twig_SimpleFilter('project_game_date_time', [$this, 'projectGameDateTimeFilter']),
				new \Twig_SimpleFilter('project_game_length', [$this, 'projectGameLengthFilter']),
				new \Twig_SimpleFilter('project_game_number', [$this, 'projectGameNumberFilter']),
				new \Twig_SimpleFilter('project_game_status', [$this, 'projectGameStatusFilter']),
				new \Twig_SimpleFilter('project_game_location_name', [$this, 'projectGameLocationNameFilter']),
				new \Twig_SimpleFilter('project_game_teams_score', [$this, 'projectGameTeamsScoreFilter']),
				new \Twig_SimpleFilter('project_game_teams_sorted', [$this, 'projectGameTeamsSortedFilter']),
				new \Twig_SimpleFilter('project_game_teams_colors_match', [$this, 'projectGameTeamsColorsMatchFilter']),
				new \Twig_SimpleFilter('project_team_id', [$this, 'projectTeamIdFilter']),
				new \Twig_SimpleFilter('project_game_team_name', [$this, 'projectGameTeamNameFilter']),
				new \Twig_SimpleFilter('project_game_team_slot', [$this, 'projectGameTeamSlotFilter']),
				new \Twig_SimpleFilter('project_game_team_score', [$this, 'projectGameTeamScoreFilter']),
				new \Twig_SimpleFilter('project_game_team_colors', [$this, 'projectGameTeamColorsFilter']),
				new \Twig_SimpleFilter('project_game_officials_sorted', [$this, 'projectGameOfficialsSortedFilter']),
				new \Twig_SimpleFilter('project_game_official_name', [$this, 'projectGameOfficialNameFilter']),
				new \Twig_SimpleFilter('project_game_official_slot', [$this, 'projectGameOfficialSlotFilter']),
				new \Twig_SimpleFilter('project_game_official_email', [$this, 'projectGameOfficialEmailFilter']),
		];
	}

	/* =================================================================
	 * Project Game Stuff
	 */

	public function projectGameNumberFilter($projectGame) {
		return $projectGame['number'] ? $projectGame['number'] : $projectGame->getId();
	}

	public function projectGameDateTimeFilter($projectGame) {
		$projectGameDate = \DateTime::createFromFormat('Y-m-d', $projectGame['date']);
		$projectGameTime = \DateTime::createFromFormat('H:i:s', $projectGame['time']);

		return
						$projectGameDate->format('D M j Y') .
						' ' .
						$projectGameTime->format('h:i A');
	}

	public function projectGameLengthFilter($projectGame) {
		$length = $projectGame['length'];
		$lengthSlot = $projectGame['timeslotlength'];

		if ($length && $lengthSlot) {
			return sprintf('%d/%d minutes', $length, $lengthSlot);
		}
		if ($lengthSlot) {
			return sprintf('%d min slot', $lengthSlot);
		}
		if ($length) {
			return sprintf('%d min', $length);
		}
		return null;
	}

	public function projectGameStatusFilter($projectGame) {
		// Allow for either numeric indexes or just plain strings
		$status = (integer) $projectGame['status'];
		if (!$status)
			return $projectGame['status'];

		$statusMap = Game::getStatusValues();
		return $statusMap[$status];
	}

	public function projectGameLocationNameFilter($projectGame) {
		$location = $projectGame['location'];

		return $location['name']; // ? $location->getLongname() : null;
	}

	public function projectGameRegionLevelFilter($projectGame) {

		return $projectGame['region']['name'] . ' ' . $projectGame['level']['age'];
	}

	// FIXME jp: This now needs to look at away and home colors
	public function projectGameTeamsColorsMatchFilter($projectGame) {
		// This should probably be an array for jamborees
		$colorsFirst = null;
		foreach ($projectGame['project_game_teams'] as $projectTeam) {
			$colors = $this->projectGameTeamColorsFilter($projectTeam);
			if ($colors) {
				if ($colors === $colorsFirst) {
					return true;
				}
				$colorsFirst = $colors;
			}
		}
		return false;
	}

	/* =================================================================
	 * Project Official Stuff
	 */

	public function projectGameOfficialsSortedFilter($projectGame) {
		$projectGameOfficials = $projectGame['project_game_officials'];

		if (!is_array($projectGameOfficials)) {
			$projectGameOfficials = $projectGameOfficials->toArray();
		}
		usort($projectGameOfficials, function($projectGameOfficial1, $projectGameOfficial2) {
			$sort1 = ProjectGameOfficial::$slotMap[$projectGameOfficial1['slot']]['sort'];
			$sort2 = ProjectGameOfficial::$slotMap[$projectGameOfficial2['slot']]['sort'];
			if ($sort1 < $sort2) {
				return -1;
			}
			if ($sort1 > $sort2) {
				return 1;
			}
			return 0;
		});
		// usort strips the keys, should I put them back?
		$projectGameOfficialsMap = [];
		foreach ($projectGameOfficials as $projectOfficial) {
			$projectGameOfficialsMap[$projectOfficial['slot']] = $projectOfficial;
		}
		return $projectGameOfficialsMap;
	}

	public function projectGameOfficialSlotFilter($projectGameOfficial) {
		return ProjectGameOfficial::$slotMap[$projectGameOfficial['slot']]['title'];
	}

	public function projectGameOfficialNameFilter($projectGameOfficial) {
		if (!$projectGameOfficial['project_official']) {
			return null;
		}

		$projectOfficial = $projectGameOfficial['project_official'];

		if ($projectOfficial['name_last'] && $projectOfficial['name_first']) {
			return sprintf('%s, %s', $projectOfficial['name_last'], $projectOfficial['name_first']);
		}
		if ($projectOfficial['name_last']) {
			return $projectOfficial['name_last'];
		}
		if ($projectOfficial['name_first']) {
			return $projectOfficial['name_first'];
		}
		return null;
	}

	public function projectGameOfficialEmailFilter($projectGameOfficial) {
		if (!$projectGameOfficial['project_official']) {
			return null;
		}

		return $projectGameOfficial['project_official']['email'];
	}

	/* ==================================================================
	 * Project Team Stuff
	 */

	public function projectGameTeamsSortedFilter($projectGame) {
		$projectGameTeams = $projectGame['project_game_teams'];

		if (!is_array($projectGameTeams)) {
			$projectGameTeams = $projectGameTeams->toArray();
		}
		usort($projectGameTeams, function($projectGameTeam1, $projectGameTeam2) {
			$sort1 = ProjectGameTeam::$slotMap[$projectGameTeam1['slot']]['sort'];
			$sort2 = ProjectGameTeam::$slotMap[$projectGameTeam2['slot']]['sort'];
			if ($sort1 < $sort2) {
				return -1;
			}
			if ($sort1 > $sort2) {
				return 1;
			}
			return 0;
		});
		// usort strips the keys, should I put them back?
		$projectGameTeamsMap = [];
		foreach ($projectGameTeams as $projectTeam) {
			$projectGameTeamsMap[$projectTeam['slot']] = $projectTeam;
		}
		return $projectGameTeamsMap;
	}

	public function projectGameTeamsScoreFilter($projectGameTeams) {
		$scores = [];
		foreach ($projectGameTeams as $projectGameTeam) {
			$scores[] = $projectGameTeam['score'] !== null ? $projectGameTeam['score'] : '?';
		}
		return implode(' - ', $scores);
	}

	public function projectGameTeamSlotFilter($projectGameTeam) {
		return ProjectGameTeam::$slotMap[$projectGameTeam['slot']]['title'];
	}

	public function projectGameTeamScoreFilter($projectGameTeam) {
		$score = $projectGameTeam['score'];
		return $score !== null ? $score : '?';
	}

	public function projectGameTeamNameFilter($projectGameTeam) {
		if ($projectGameTeam['project_team']) {
			return $projectGameTeam['project_team']['name'];
		}
		return $projectGameTeam['source'];
	}

	public function projectGameTeamColorsFilter($projectGameTeam, $default = null) {
		$colorsHome = $projectGameTeam['project_team']['colors_home'];
		$colorsAway = $projectGameTeam['project_team']['colors_away'];
		switch ($projectGameTeam['slot']) {
			case 'home':
				$colors = isset($colorsHome) ? $colorsHome : null;
				break;
			case 'away':
				$colors = isset($colorsAway) ? $colorsAway : (isset($colorsHome) ? $colorsHome : null);
				break;
		}

		return $colors ?: $default;
	}

	public function projectGameTeamCoachNameFilter($projectGameTeam, $default = null) {
		$coachName = isset($projectGameTeam['project_team']['coach_name']) ?
						$projectGameTeam['project_team']['coach_name'] :
						null;
		return $coachName ?: $default;
	}

	public function projectGameTeamCoachEmailFilter($projectGameTeam, $default = null) {
		$coachName = isset($projectGameTeam['project_team']['coach_email']) ?
						$projectGameTeam['project_team']['coach_email'] :
						null;
		return $coachName ?: $default;
	}

	public function projectTeamIdFilter($projectGameTeam) {
		if ($projectGameTeam['projectTeam']) {
			return $projectGameTeam['projectTeam']->getId();
		}
		return null;
	}

}
