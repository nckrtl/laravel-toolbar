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
export type LaravelData = {
version: string | null;
environment: string | null;
timezone: string | null;
locale: string | null;
debug: string | null;
};
export type ModelData = {
action: string;
model: string;
count: number;
};
export type PhpData = {
version: string;
memory_limit: string;
max_execution_time: string;
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
uuid: string | null;
memory: number | null;
duration: number | null;
};
export type RequestStageData = {
recordedStart: boolean;
recordedEnd: boolean;
wall_time: NckRtl.Toolbar.Data.RequestStagePropertyData;
memory_real_delta: NckRtl.Toolbar.Data.RequestStagePropertyData | null;
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
export type TimingData = {
total_ms: number;
stages: Array<any>;
};
export type ToolbarConfig = {
debug: boolean;
observers: Array<any>;
collectors: Array<any>;
};
export type VueData = {
version: string | null;
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
export type VueConfig = {
enabled: boolean;
};
}
