declare namespace NckRtl.Toolbar.Data {
export type BootProfileData = {
before_autoload: Array<any>;
after_autoload: Array<any>;
after_bootstrap: Array<any>;
providers_registered: Array<any>;
providers_booted: Array<any>;
};
export type DatabaseData = {
tablePlusConnectionUrl: string | null;
name: string;
connection: string;
driver: string;
};
export type InertiaData = {
version: string | null;
};
export type LaravelData = {
version: string | null;
version_editor_url: string | null;
environment: string | null;
environment_editor_url: string | null;
timezone: string | null;
timezone_editor_url: string | null;
locale: string | null;
locale_editor_url: string | null;
debug: string | null;
debug_editor_url: string | null;
};
export type ModelData = {
action: string;
model: string;
count: number;
memory_used: any | null;
};
export type PhpData = {
version: string;
memory_limit: string;
max_execution_time: string;
};
export type ProfileMarkerData = {
label: string;
memory_real: any | null;
time: any | null;
};
export type ProfilerData = {
total_wall_time: any;
total_real_memory: any;
total_allocated_memory: any;
stages: Array<any>;
};
export type QueriesData = {
totalTime: number;
totalTimeFilteredQueries: number;
databases: Array<any>;
connections: Array<any>;
drivers: Array<any>;
queries: Array<any>;
};
export type QueryData = {
controller_action_editor_url: string | null;
type: any | null;
hash: string;
sql: string;
bindings: Array<any>;
duration: number;
connection: string;
driver: string;
is_duplicate: boolean;
is_slow: boolean;
percentage: number;
offset: number;
memory_used: any | null;
file: string | null;
line: number | null;
};
export type RequestCheckpointData = {
memory_real: any | null;
time: any | null;
measureMemory: boolean;
};
export type RequestData = {
route_name: string;
controller_action_editor_url: string | null;
route_editor_url: string | null;
method: string;
uri: string;
ip_address: string;
controller_action: string;
middleware: Array<any>;
is_inertia: boolean;
uuid: string | null;
memory: number | null;
duration: number | null;
};
export type RequestStageData = {
recordedStart: boolean;
recordedEnd: boolean;
wall_time: NckRtl.Toolbar.Data.RequestStagePropertyData;
memory_real_delta: NckRtl.Toolbar.Data.RequestStagePropertyData | null;
substages: Array<NckRtl.Toolbar.Data.SubstageData>;
label: string;
start: NckRtl.Toolbar.Data.RequestCheckpointData | null;
end: NckRtl.Toolbar.Data.RequestCheckpointData | null;
color: string;
filesInvolved: Array<any>;
};
export type RequestStagePropertyData = {
percentage: number;
measurement: any;
};
export type ResponseData = {
status_code: number;
headers: Array<any>;
size: any;
};
export type SubstageData = {
wall_time: NckRtl.Toolbar.Data.RequestStagePropertyData | null;
memory_real_delta: NckRtl.Toolbar.Data.RequestStagePropertyData | null;
label: string;
start: NckRtl.Toolbar.Data.ProfileMarkerData;
end: NckRtl.Toolbar.Data.ProfileMarkerData;
};
export type TailwindData = {
version: string | null;
};
export type TimingData = {
total_ms: number;
stages: Array<any>;
};
export type ToolbarConfig = {
debug: boolean;
observers: Array<any>;
collectors: Array<any>;
enabledInConsole: boolean;
isVueDevtoolsEnabled: boolean;
layout: NckRtl.Toolbar.Data.Layout.LayoutConfig;
};
export type VueData = {
version: string | null;
version_editor_url: string | null;
devtools_version: string | null;
};
}
declare namespace NckRtl.Toolbar.Data.CollectorConfigurations {
export type RequestConfurationData = {
enabled: boolean;
provider: any | null;
};
}
declare namespace NckRtl.Toolbar.Data.Configurations {
export type InertiaConfig = {
enabled: boolean;
};
export type LaravelConfig = {
enabled: boolean;
version: boolean;
environment: boolean;
debug: boolean;
timezone: boolean;
locale: boolean;
};
export type MiddlewareConfig = {
prepend: Array<any>;
append: Array<any>;
enabled: boolean;
};
export type ModelsConfig = {
enabled: boolean;
};
export type PhpConfig = {
enabled: boolean;
};
export type ProfilerConfig = {
enabled: boolean;
};
export type QueriesConfig = {
enabled: boolean;
showSessionQueries: boolean;
};
export type RequestConfig = {
enabled: boolean;
dataProvider: any | null;
};
export type ResponseConfig = {
enabled: boolean;
};
export type TailwindConfig = {
enabled: boolean;
};
export type VueConfig = {
enabled: boolean;
};
}
declare namespace NckRtl.Toolbar.Data.Layout {
export type GroupConfig = {
tools: Array<any>;
section: any;
};
export type LayoutConfig = {
sections: Array<any>;
};
export type ToolConfig = {
};
}
declare namespace NckRtl.Toolbar.Data.Tools {
export type BreakpointIndicatorTool = {
show_pixels: boolean;
};
export type DatabaseTool = {
};
export type LaravelTool = {
show_version: boolean;
};
export type MemoryUsageTool = {
};
export type ModelsTool = {
};
export type RequestTool = {
show_status: boolean;
url: boolean;
};
export type TechStackTool = {
laravel: boolean;
vue: boolean;
php: boolean;
inertia: boolean;
};
export type TimingsTool = {
};
export type VueDevtoolsTool = {
};
export type VueInspectorTool = {
};
export type VueTool = {
show_version: boolean;
};
}
