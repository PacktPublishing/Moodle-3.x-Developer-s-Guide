-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Mar 27, 2017 at 08:56 AM
-- Server version: 5.6.17
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `test`
--

-- --------------------------------------------------------

--
-- Table structure for table `framework`
--

CREATE TABLE IF NOT EXISTS `framework` (
  `parentid` varchar(9) DEFAULT NULL,
  `idnumber` varchar(11) DEFAULT NULL,
  `shortname` varchar(76) DEFAULT NULL,
  `description` varchar(372) DEFAULT NULL,
  `descriptionformat` int(1) DEFAULT NULL,
  `scalevalues` varchar(41) DEFAULT NULL,
  `scaleconfig` varchar(99) DEFAULT NULL,
  `ruletype` varchar(35) DEFAULT NULL,
  `ruleoutcome` varchar(1) DEFAULT NULL,
  `ruleconfig` varchar(4) DEFAULT NULL,
  `crossref` varchar(3) DEFAULT NULL,
  `isframework` varchar(1) DEFAULT NULL,
  `taxonomy` varchar(43) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `framework`
--

INSERT INTO `framework` (`parentid`, `idnumber`, `shortname`, `description`, `descriptionformat`, `scalevalues`, `scaleconfig`, `ruletype`, `ruleoutcome`, `ruleconfig`, `crossref`, `isframework`, `taxonomy`) VALUES
('', 'MN-2016-6v2', 'MNPCC Grade 6 Science', '<p>Minnesota Partnership for Collaborative Curriculum Grade 6 Science Competencies. <a href="http://education.state.mn.us/MDE/dse/stds/sci/" target="_blank">Click here for source document</a>.<br></p>', 1, 'Not satisfactory,Satisfactory,Outstanding', '[{"scaleid":"7"},{"id":2,"scaledefault":1,"proficient":1},{"id":3,"scaledefault":0,"proficient":1}]', '', '', '', '', '1', 'competency,competency,competency,competency'),
('', '6.1.2.0.0', 'The Nature of Science and Engineering', '<p>The Nature of Science and Engineering<br /></p>', 1, '', '', 'core_competency\\competency_rule_all', '2', 'null', '255', '', ''),
('6.1.2.0.0', '6.1.2.1.0', 'The Practice of Engineering', '1. Engineers create, develop and manufacture machines, structures, processes and systems that impact society and may make humans more productive.<br />2. Engineering design is the process of devising products, processes and systems that address a need, capitalize on an opportunity, or solve a specific problem.', 1, '', '', 'core_competency\\competency_rule_all', '2', 'null', '256', '', ''),
('6.1.2.1.0', '6.1.2.1.1', 'A (6.1.2.1.1)', '<p>Identify a common engineered system and evaluate its impact on the daily life of humans.&nbsp; For example: Refrigeration, cell phone, or automobile.<br></p>', 1, '', '', '', '0', 'null', '257', '', ''),
('6.1.2.1.0', '6.1.2.1.2', 'A (6.1.2.1.2)', 'Recognize that there is no perfect design and that new technologies have consequences that may increase some risks and decrease others. For example: Seat belts and airbags.', 1, '', '', '', '0', 'null', '258', '', ''),
('6.1.2.1.0', '6.1.2.1.3', 'A (6.1.2.1.3)', '<p>Describe the trade-offs in using manufactured products in terms of features, performance, durability and cost.<br></p>', 1, '', '', '', '0', 'null', '259', '', ''),
('6.1.2.1.0', '6.1.2.1.4', 'A (6.1.2.1.4)', 'Explain the importance of learning from past failures, in order to inform future designs of similar products or systems. For example: Space shuttle or bridge design.', 1, '', '', '', '0', 'null', '260', '', ''),
('6.1.2.1.0', '6.1.2.2.1', 'A (6.1.2.2.1)', 'Apply and document an engineering design process that includes identifying criteria and constraints, making representations, testing and evaluation, and refining the design as needed to construct a product or system to solve a problem. For example: Investigate how energy changes from one form to another by designing and constructing a simple roller coaster for a marble.', 1, '', '', '', '0', 'null', '261', '', ''),
('6.1.2.0.0', '6.1.3.1.0', 'Interactions Among Science, Technology, Engineering, Mathematics and Society', '1. Designed and natural systems exist in the world. These systems consist of components that act within the system and interact with other systems.<br />4. Current and emerging technologies have enabled humans to develop and use models to understand and communicate how natural and designed systems work and interact.', 1, '', '', 'core_competency\\competency_rule_all', '2', 'null', '262', '', ''),
('6.1.3.1.0', '6.1.3.1.1', 'A (6.1.3.1.1)', 'Describe a system in terms of its subsystems and parts, as well as its inputs, processes and outputs.', 1, '', '', '', '0', 'null', '263', '', ''),
('6.1.3.1.0', '6.1.3.1.2', 'A (6.1.3.1.2)', 'Distinguish between open and closed systems.&nbsp; For example: Compare mass before and after a chemical reaction that releases a gas in sealed and open plastic bags.', 1, '', '', '', '0', 'null', '264', '', ''),
('6.1.3.1.0', '6.1.3.4.1', 'A (6.1.3.4.1)', 'Determine and use appropriate safe procedures, tools, measurements, graphs, and mathematical analyses to describe and investigate natural and designed systems in a physical science context.', 1, '', '', '', '0', 'null', '265', '', ''),
('6.1.3.1.0', '6.1.3.4.2', 'A (6.1.3.4.2)', '<p>Demonstrate the conversion of units within the International System of Units (S.I. or metric) and estimate the magnitude of common objects and quantities using metric units.<br></p>', 1, '', '', '', '0', 'null', '284', '', ''),
('', '6.2.1.0.0', 'Physical Science', '<p>Physical Science<br /></p>', 1, '', '', 'core_competency\\competency_rule_all', '2', 'null', '266', '', ''),
('6.2.1.0.0', '6.2.1.1.0', 'Matter', '<p>1. Pure substances can be identified by properties which are independent of the sample of the substance and the properties can be explained by a model of matter that is composed of small particles.<br />2. Substances can undergo physical changes which do not change the composition or the total mass of the substance in a closed system.<br /></p>', 1, '', '', 'core_competency\\competency_rule_all', '2', 'null', '285', '', ''),
('6.2.1.1.0', '6.2.1.1.1', 'B (6.2.1.1.1)', '<p>Explain density, dissolving, compression, diffusion and thermal expansion using the particle model of matter.<br></p>', 1, '', '', '', '0', 'null', '287', '', ''),
('6.2.1.1.0', '6.2.1.2.1', 'B (6.2.1.2.1)', '<p>Identify evidence of physical changes, including changing phase or shape, and dissolving in other materials.<br></p>', 1, '', '', '', '0', 'null', '288', '', ''),
('6.2.1.1.0', '6.2.1.2.2', 'B (6.2.1.2.2)', '<p>Describe how mass is conserved during a physical change in a closed system.&nbsp; For example: The mass of an ice cube does not change when it melts.<br></p>', 1, '', '', '', '0', 'null', '289', '', ''),
('6.2.1.1.0', '6.2.1.2.3', 'B (6.2.1.2.3)', '<p>Use the relationship between heat and the motion and arrangement of particles in solids, liquids and gases to explain melting, freezing, condensdation and evaporation.<br></p>', 1, '', '', '', '0', 'null', '290', '', ''),
('6.2.1.0.0', '6.2.2.1.0', 'Motion', '<p>1. The motion of an object can be described in terms of speed, direction and change of position.<br />2. Forces have magnitude and direction and affect the motion of objects.<br /></p>', 1, '', '', 'core_competency\\competency_rule_all', '2', 'null', '286', '', ''),
('6.2.2.1.0', '6.2.2.1.1', 'B (6.2.2.1.1)', '<p>Measure and calculate the speed of an object that is traveling in a straight line.<br></p>', 1, '', '', '', '0', 'null', '291', '', ''),
('6.2.2.1.0', '6.2.2.1.2', 'B (6.2.2.1.2)', '<p>For an object traveling in a straight line, graph the object’s position as a function of time, and its speed as a function of time.&nbsp; Explain how these graphs describe the object’s motion.<br></p>', 1, '', '', '', '0', 'null', '292', '', ''),
('6.2.2.1.0', '6.2.2.2.1', 'B (6.2.2.2.1)', '<p>Recognize that when the forces acting on an object are balanced, the object remains at rest or continues to move at a constant speed in a straight line, and that unbalanced forces cause a change in the speed or direction of the motion of an object.<br></p>', 1, '', '', '', '0', 'null', '293', '', ''),
('6.2.2.1.0', '6.2.2.2.2', 'B (6.2.2.2.2)', '<p>Identify the forces acting on an object and describe how the sum of the forces affects the motion of the object.&nbsp; For example: Forces acting on a book on a table or a car on the road.<br></p>', 1, '', '', '', '0', 'null', '294', '', ''),
('6.2.2.1.0', '6.2.2.2.3', 'B (6.2.2.2.3)', '<p>Recognize that some forces between objects act when the objects are in direct contact and others, such as magnetic, electrical, and gravitational forces can act from a distance.<br></p>', 1, '', '', '', '0', 'null', '295', '', ''),
('6.2.2.1.0', '6.2.2.2.4', 'B (6.2.2.2.4)', '<p>Distinguish between mass and weight.<br></p>', 1, '', '', '', '0', 'null', '296', '', ''),
('6.2.1.0.0', '6.2.3.1.0', 'Energy', '<p>1. Waves involve the transfer of energy without the transfer of matter.<br />2. Energy can be transformed within a system or transferred to other systems or the environment.<br /></p>', 1, '', '', 'core_competency\\competency_rule_all', '2', 'null', '267', '', ''),
('6.2.3.1.0', '6.2.3.1.1', 'B (6.2.3.1.1)', 'Describe properties of waves, including speed, wavelength, frequency and amplitude.', 1, '', '', '', '0', 'null', '268', '', ''),
('6.2.3.1.0', '6.2.3.1.2', 'B (6.2.3.1.2)', 'Explain how the vibration of particles in air and other materials results in the transfer of energy through sound waves.', 1, '', '', '', '0', 'null', '269', '', ''),
('6.2.3.1.0', '6.2.3.1.3', 'B (6.2.3.1.3)', 'Use wave properties of light to explain reflection, refraction and the color spectrum. <br>', 1, '', '', '', '0', 'null', '270', '', ''),
('6.2.3.1.0', '6.2.3.2.1', 'B (6.2.3.2.1)', '<p>Differentiate between kinetic and potential energy and analyze situations where kinetic energy is converted to potential energy and vice versa.<br></p>', 1, '', '', '', '0', 'null', '297', '', ''),
('6.2.3.1.0', '6.2.3.2.2', 'B (6.2.3.2.2)', '<p>Trace the changes of energy forms, including thermal, electrical, chemical, mechanical or others as energy is used in devices.&nbsp; For example: A bicycle, light bulb or automobile.<br></p>', 1, '', '', '', '0', 'null', '298', '', ''),
('6.2.3.1.0', '6.2.3.2.3', 'B (6.2.3.2.3)', '<p>Describe how heat energy is transferred in conduction, convection and radiation.&nbsp; <br></p>', 1, '', '', '', '0', 'null', '299', '', '');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
